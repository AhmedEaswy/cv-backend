<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Models\Profile;
use App\Models\Template;
use App\Services\CVPDFService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

class PrintProfile extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = ProfileResource::class;

    protected string $view = 'filament.resources.profiles.pages.print-profile';

    public ?array $data = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->data = [
            'profile_name' => $this->record->name,
            'user_id' => $this->record->user_id,
            'template_id' => null,
        ];
    }

    public function getRecord(): Profile
    {
        return $this->record;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('profile_name')
                    ->label('Profile Name')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('user_id')
                    ->label('User ID')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('template_id')
                    ->label('Template')
                    ->options(Template::where('is_active', true)
                        ->get()
                        ->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
            ])
            ->statePath('data');
    }

    public function cancel(): \Illuminate\Http\RedirectResponse
    {
        return redirect(ProfileResource::getUrl('view', ['record' => $this->getRecord()]));
    }

    public function generatePdf()
    {
        // Get form data directly from $data property (since we use statePath('data'))
        $data = $this->data;
        $templateId = $data['template_id'] ?? null;
        $profile = $this->getRecord();

        if (! $templateId) {
            Notification::make()
                ->title('Template Required')
                ->body('Please select a template before generating the PDF.')
                ->danger()
                ->send();

            return redirect()->back();
        }

        $template = Template::where('id', $templateId)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            Notification::make()
                ->title('Template Not Found')
                ->body('The selected template is not available.')
                ->danger()
                ->send();

            return redirect()->back();
        }

        /** @var CVPDFService $pdfService */
        $pdfService = app(CVPDFService::class);

        try {
            // Always generate URL for Filament dashboard and redirect to it
            $url = $pdfService->generatePdf($profile, $template, true);

            Notification::make()
                ->title('PDF Generated')
                ->body('The CV PDF has been generated successfully.')
                ->success()
                ->send();

            return redirect()->away($url);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('An error occurred while generating the PDF: ' . $e->getMessage())
                ->danger()
                ->send();

            return redirect()->back();
        }
    }

    private function formatProfileResponse($profile): array
    {
        // Use the same mapping logic as CVController
        $userData = [];

        // Map info back to user_data format
        if ($profile->info) {
            $info = $profile->info;
            $userData['firstName'] = $info['firstName'] ?? null;
            $userData['lastName'] = $info['lastName'] ?? null;
            $userData['jobTitle'] = $info['jobTitle'] ?? null;
            $userData['email'] = $info['email'] ?? null;
            $userData['address'] = $info['address'] ?? null;
            $userData['portfolioUrl'] = $info['portfolioUrl'] ?? null;
            $userData['phone'] = $info['phone'] ?? null;
            $userData['summary'] = $info['summary'] ?? null;
            $userData['birthdate'] = $info['birthdate'] ?? null;

            if (isset($info['skills'])) {
                $userData['skills'] = $info['skills'];
            }
        }

        // Map educations (already in correct format)
        if ($profile->educations) {
            $userData['educations'] = $profile->educations;
        }

        // Map experiences: Profile uses "name", API uses "company"
        if ($profile->experiences) {
            $userData['experiences'] = array_map(function ($exp) {
                return [
                    'position' => $exp['position'] ?? null,
                    'company' => $exp['name'] ?? null,
                    'location' => $exp['location'] ?? null,
                    'description' => $exp['description'] ?? null,
                    'from' => $exp['from'] ?? null,
                    'to' => $exp['to'] ?? null,
                    'current' => $exp['currentlyWorkingHere'] ?? false,
                ];
            }, $profile->experiences);
        }

        // Map projects: Profile uses "name", API uses "title"
        if ($profile->projects) {
            $userData['projects'] = array_map(function ($proj) {
                return [
                    'title' => $proj['name'] ?? null,
                    'description' => $proj['description'] ?? null,
                    'url' => $proj['url'] ?? null,
                    'from' => $proj['from'] ?? null,
                    'to' => $proj['to'] ?? null,
                ];
            }, $profile->projects);
        }

        // Map languages: Profile uses "language" and "level", API uses "name" and "proficiencyLevel"
        if ($profile->languages) {
            $levelMap = [
                'beginner' => 1,
                'intermediate' => 2,
                'advanced' => 3,
                'fluent' => 4,
                'native' => 5,
            ];
            $userData['languages'] = array_map(function ($lang) use ($levelMap) {
                return [
                    'name' => $lang['language'] ?? null,
                    'proficiencyLevel' => $levelMap[$lang['level']] ?? 1,
                ];
            }, $profile->languages);
        }

        // Map interests: Profile uses "interest", API uses "name"
        if ($profile->interests) {
            $userData['interests'] = array_map(function ($interest) {
                return [
                    'name' => $interest['interest'] ?? null,
                ];
            }, $profile->interests);
        }

        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $profile->name,
            'language' => $profile->language,
            'sections_order' => $profile->sections_order,
            'user_data' => $userData,
            'created_at' => $profile->created_at?->toIso8601String(),
            'updated_at' => $profile->updated_at?->toIso8601String(),
        ];
    }
}

