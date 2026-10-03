<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use Illuminate\Support\Facades\App;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\Response;

class CoverLetterPDFService
{
    public function __construct(
        private CoverLetterDataMapper $dataMapper,
        private GeneratedPdfStore $pdfStore,
    ) {}

    public function generatePdf(CoverLetter $coverLetter, CoverLetterTemplate $template, bool $returnUrl = false): Response|string
    {
        $viewName = strtolower(str_replace(' ', '-', $template->name));
        $viewPath = "templates.cover-letter.{$viewName}";

        if (! view()->exists($viewPath)) {
            throw new \RuntimeException(__('messages.template_view_not_found'));
        }

        $data = $this->dataMapper->formatCoverLetterResponse($coverLetter);
        App::setLocale($data['language'] ?? 'en');

        try {
            $filenameBase = ($data['user_data']['firstName'] ?? 'Cover').'_'.($data['user_data']['lastName'] ?? 'Letter');
            $downloadFilename = $filenameBase.'.pdf';

            if (! $returnUrl) {
                return $this->makePdf($viewPath, $data)->download($downloadFilename);
            }

            return $this->pdfStore->put(
                $coverLetter,
                'cover-letters',
                'cl',
                $this->pdfStore->renderContext($template, $viewPath),
                $data,
                function (string $absolutePath) use ($viewPath, $data): void {
                    $this->makePdf($viewPath, $data)->save($absolutePath);
                },
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(__('messages.pdf_generation_failed').': '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makePdf(string $viewPath, array $data): \Spatie\LaravelPdf\PdfBuilder
    {
        return Pdf::view($viewPath, ['coverLetter' => $data])
            ->format('a4')
            ->margins(10, 10, 10, 10)
            ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                $browsershot->setOption('args', [
                    '--disable-dev-shm-usage',
                    '--disable-gpu',
                    '--disable-setuid-sandbox',
                    '--disable-software-rasterizer',
                ]);
            });
    }

    public function createTemporaryCoverLetter(array $userData, ?int $userId = null, ?string $name = 'Cover Letter', ?string $language = 'en', ?array $sectionsOrder = null): CoverLetter
    {
        $mappedData = app(CoverLetterDataMapper::class)->mapUserDataToCoverLetter($userData);

        return new CoverLetter([
            'user_id' => $userId,
            'name' => $name,
            'language' => $language,
            'sections_order' => $sectionsOrder,
            'info' => $mappedData['info'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
        ]);
    }
}
