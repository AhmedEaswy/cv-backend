<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Template;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\ViewException;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Pdf\Exceptions\InvalidFormat;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CVPDFService
{
    public function __construct(
        private CVDataMapper $dataMapper
    ) {
    }

    /**
     * Generate PDF from Profile and Template.
     *
     * When $returnUrl is false (default): returns a download Response.
     * When $returnUrl is true: saves to storage and returns a public URL string.
     *
     * @return Response|string
     *
     * @throws \Exception
     */
    public function generatePdf(Profile $profile, Template $template, bool $returnUrl = false): Response|string
    {
        // Convert template name to view name (kebab-case)
        $viewName = strtolower(str_replace(' ', '-', $template->name));
        $viewPath = "templates.cv.{$viewName}";

        // Check if view exists
        if (!view()->exists($viewPath)) {
            Log::error('PDF Generation: View not found', [
                'view_path' => $viewPath,
                'template_id' => $template->id,
                'template_name' => $template->name,
            ]);

            throw new \RuntimeException('Template view not found. Please check server configuration.');
        }

        // Format profile data for view
        $cvData = $this->dataMapper->formatProfileResponse($profile);

        try {
            $pdf = Pdf::view($viewPath, ['cv' => $cvData])
                ->format('a4')
                ->margins(10, 10, 10, 10)
                ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                    // --no-sandbox is automatically added by LARAVEL_PDF_NO_SANDBOX config
                    $browsershot->setOption('args', [
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-setuid-sandbox',
                        '--disable-software-rasterizer',
                    ]);
                });

            $filenameBase = ($cvData['user_data']['firstName'] ?? 'CV') . '_' . ($cvData['user_data']['lastName'] ?? 'Resume');
            $downloadFilename = $filenameBase . '.pdf';

            if (! $returnUrl) {
                return $pdf->download($downloadFilename);
            }

            // Save to public disk and return URL
            $storedFilename = uniqid('cv_') . '.pdf';
            $relativePath = 'cvs/' . $storedFilename;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (! is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0775, true);
            }

            $pdf->save($fullPath);

            return Storage::disk('public')->url($relativePath);
        } catch (ViewException $e) {
            Log::error('PDF Generation (URL): View Exception', [
                'message' => $e->getMessage(),
                'view_path' => $viewPath,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw new \RuntimeException('View error - ' . $e->getMessage(), 0, $e);
        } catch (InvalidFormat $e) {
            Log::error('PDF Generation (URL): Invalid Format', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Invalid PDF format - ' . $e->getMessage(), 0, $e);
        } catch (ProcessFailedException $e) {
            Log::error('PDF Generation (URL): Process Failed', [
                'message' => $e->getMessage(),
                'command' => method_exists($e->getProcess(), 'getCommandLine') ? $e->getProcess()->getCommandLine() : 'N/A',
            ]);
            throw new \RuntimeException('PDF generation process failed. Please ensure Chrome/Chromium and Node.js are installed on the server.', 0, $e);
        } catch (\Exception $e) {
            Log::error('PDF Generation (URL): General Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a temporary Profile from user_data array.
     */
    public function createTemporaryProfile(array $userData, ?int $userId = null, ?string $name = 'CV', ?string $language = 'en', ?array $sectionsOrder = null): Profile
    {
        $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

        return new Profile([
            'user_id' => $userId,
            'name' => $name,
            'language' => $language,
            'sections_order' => $sectionsOrder,
            'info' => $mappedData['info'] ?? null,
            'interests' => $mappedData['interests'] ?? null,
            'languages' => $mappedData['languages'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
            'projects' => $mappedData['projects'] ?? null,
            'educations' => $mappedData['educations'] ?? null,
        ]);
    }
}


