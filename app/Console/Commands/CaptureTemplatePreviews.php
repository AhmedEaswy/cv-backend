<?php

namespace App\Console\Commands;

use App\Models\CoverLetterTemplate;
use App\Models\PublicProfileTemplate;
use App\Models\Template;
use App\Support\TemplatePreviewSample;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;
use Throwable;

class CaptureTemplatePreviews extends Command
{
    protected $signature = 'templates:capture-previews
        {--type=all : cv, cover-letter, public-profile, or all}
        {--locale=all : en, ar, or all}
        {--only= : Comma-separated template slugs}
        {--sync-only : Point database rows at existing images without recapturing}
        {--force : Replace a preview that was uploaded in the dashboard}';

    protected $description = 'Render each template with sample data and save a preview image into public/images';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        $locales = $this->locales((string) $this->option('locale'));

        if ($locales === []) {
            $this->error('Invalid --locale. Use en, ar, or all.');

            return self::FAILURE;
        }

        $only = array_values(array_filter(array_map(
            fn (string $slug) => strtolower(trim($slug)),
            explode(',', (string) $this->option('only'))
        )));

        $targets = $this->targets($type, $only);

        if ($targets === []) {
            $this->error('No templates matched. Use --type=cv|cover-letter|public-profile|all and an optional --only=slug.');

            return self::FAILURE;
        }

        $failures = 0;
        $previousLocale = app()->getLocale();

        foreach ($locales as $locale) {
            app()->setLocale($locale);

            foreach ($targets as $target) {
                $filename = $locale === 'ar'
                    ? $target['slug'].'-ar.png'
                    : $target['slug'].'.png';
                $relative = $target['public'].'/'.$filename;
                $absolute = public_path($relative);

                if (! $this->option('sync-only')) {
                    try {
                        File::ensureDirectoryExists(dirname($absolute));
                        $this->capture($target, $absolute, $locale);
                        $this->line("saved  {$relative}");
                    } catch (Throwable $e) {
                        $failures++;
                        $this->error("failed {$locale}/{$target['kind']}/{$target['slug']}: {$e->getMessage()}");

                        continue;
                    }
                } elseif (! is_file($absolute)) {
                    $failures++;
                    $this->error("missing {$relative} — run without --sync-only to capture it");

                    continue;
                }

                $this->syncPreview($target['model'], $target['slug'], $relative, $locale);
            }
        }

        app()->setLocale($previousLocale);

        if ($failures > 0) {
            $this->warn("{$failures} template(s) were not captured.");

            return self::FAILURE;
        }

        $this->info('Preview images are in public/images. Commit them so production uses the same files.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function locales(string $locale): array
    {
        return match ($locale) {
            'en' => ['en'],
            'ar' => ['ar'],
            'all' => ['en', 'ar'],
            default => [],
        };
    }

    /**
     * @return list<array{kind: string, slug: string, view: string, public: string, model: class-string<Model>, width: int, height: int, scale: int, selector: ?string}>
     */
    private function targets(string $type, array $only): array
    {
        $kinds = [
            'cv' => [
                'directory' => resource_path('views/templates/cv'),
                'view' => 'templates.cv',
                'public' => 'images/cv-templates',
                'model' => Template::class,
                'width' => 1400,
                'height' => 1800,
                'scale' => 2,
                'selector' => '.page',
            ],
            'cover-letter' => [
                'directory' => resource_path('views/templates/cover-letter'),
                'view' => 'templates.cover-letter',
                'public' => 'images/templates',
                'model' => CoverLetterTemplate::class,
                'width' => 1400,
                'height' => 1800,
                'scale' => 2,
                'selector' => '.page',
            ],
            'public-profile' => [
                'directory' => resource_path('views/templates/public-profile'),
                'view' => 'templates.public-profile',
                'public' => 'images/public-profile-templates',
                'model' => PublicProfileTemplate::class,
                'width' => 1100,
                'height' => 1467,
                'scale' => 1,
                'selector' => null,
            ],
        ];

        if ($type !== 'all' && ! isset($kinds[$type])) {
            return [];
        }

        $selected = $type === 'all' ? $kinds : [$type => $kinds[$type]];
        $targets = [];

        foreach ($selected as $kind => $config) {
            foreach (File::files($config['directory']) as $file) {
                $slug = $file->getBasename('.blade.php');

                if (str_starts_with($slug, '_')) {
                    continue;
                }

                if ($only !== [] && ! in_array($slug, $only, true)) {
                    continue;
                }

                $view = $config['view'].'.'.$slug;

                if (! view()->exists($view)) {
                    continue;
                }

                $targets[] = [
                    'kind' => $kind,
                    'slug' => $slug,
                    'view' => $view,
                    'public' => $config['public'],
                    'model' => $config['model'],
                    'width' => $config['width'],
                    'height' => $config['height'],
                    'scale' => $config['scale'],
                    'selector' => $config['selector'],
                ];
            }
        }

        return $targets;
    }

    /**
     * @param  array{kind: string, slug: string, view: string, width: int, height: int, scale: int, selector: ?string}  $target
     */
    private function capture(array $target, string $absolute, string $locale): void
    {
        $html = view($target['view'], $this->viewData($target['kind'], $locale))->render();
        $html = str_replace(
            '</head>',
            '<style>::-webkit-scrollbar{display:none} html{scrollbar-width:none}</style></head>',
            $html
        );

        $shot = Browsershot::html($html)
            ->windowSize($target['width'], $target['height'])
            ->deviceScaleFactor($target['scale'])
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->delay(300)
            ->timeout(90);

        if ($target['selector']) {
            $shot->select($target['selector']);
        }

        $shot->setOption('args', [
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-setuid-sandbox',
            '--disable-software-rasterizer',
        ]);

        $chrome = config('laravel-pdf.browsershot.chrome_path');
        $node = config('laravel-pdf.browsershot.node_binary');

        if (is_string($chrome) && $chrome !== '') {
            $shot->setChromePath($chrome);
        }

        if (is_string($node) && $node !== '') {
            $shot->setNodeBinary($node);
        }

        if (config('laravel-pdf.browsershot.no_sandbox')) {
            $shot->noSandbox();
        }

        $shot->save($absolute);
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(string $kind, string $locale): array
    {
        return match ($kind) {
            'cv' => ['cv' => TemplatePreviewSample::cv($locale), 'preview' => false],
            'cover-letter' => ['coverLetter' => TemplatePreviewSample::coverLetter($locale), 'preview' => false],
            'public-profile' => ['profile' => TemplatePreviewSample::publicProfile($locale), 'preview' => false],
            default => [],
        };
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function syncPreview(string $model, string $slug, string $relative, string $locale): void
    {
        /** @var Model|null $record */
        $record = $model::query()->where('name', $slug)->first();

        if (! $record) {
            $this->warn("no database row for {$slug}; image saved only");

            return;
        }

        $column = $locale === 'ar' ? 'preview_ar' : 'preview';
        $current = $record->getAttribute($column);

        if (! $this->shouldReplacePreview(is_string($current) ? $current : null)) {
            $this->line("kept   {$slug} {$column} (dashboard upload; pass --force to replace)");

            return;
        }

        $record->update([$column => $relative]);
        $this->line("synced {$slug} {$column}");
    }

    private function shouldReplacePreview(?string $current): bool
    {
        if ($this->option('force') || $current === null || $current === '') {
            return true;
        }

        if (str_starts_with($current, 'http://') || str_starts_with($current, 'https://')) {
            return false;
        }

        // Shipped files under public/images are the ones this command owns.
        if (str_starts_with($current, 'images/')) {
            return true;
        }

        return ! is_file(storage_path('app/public/'.$current));
    }
}
