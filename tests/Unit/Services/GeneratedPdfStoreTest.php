<?php

namespace Tests\Unit\Services;

use App\Models\CoverLetter;
use App\Models\Profile;
use App\Models\User;
use App\Services\GeneratedPdfStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeneratedPdfStoreTest extends TestCase
{
    use RefreshDatabase;

    private GeneratedPdfStore $store;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->store = app(GeneratedPdfStore::class);
    }

    public function test_reuses_existing_pdf_when_inputs_are_unchanged(): void
    {
        $profile = $this->profile();
        $writes = 0;
        $payload = ['language' => 'en', 'user_data' => ['firstName' => 'Ada']];
        $context = ['template_id' => 1];

        $firstUrl = $this->store->put(
            $profile,
            'cvs',
            'cv',
            $context,
            $payload,
            function (string $path) use (&$writes): void {
                $writes++;
                file_put_contents($path, 'pdf-v1');
            }
        );

        $secondUrl = $this->store->put(
            $profile->fresh(),
            'cvs',
            'cv',
            $context,
            $payload,
            function (string $path) use (&$writes): void {
                $writes++;
                file_put_contents($path, 'pdf-v2');
            }
        );

        $stored = $profile->fresh();

        $this->assertSame(1, $writes);
        $this->assertSame($firstUrl, $secondUrl);
        $this->assertSame('pdf-v1', Storage::disk('public')->get($stored->pdf_path));
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'deleted_at' => null,
        ]);
    }

    public function test_replaces_cv_pdf_and_deletes_the_previous_file(): void
    {
        $profile = $this->profile()->fresh();
        $updatedAt = $profile->updated_at->clone();

        $this->store->put(
            $profile,
            'cvs',
            'cv',
            ['template_id' => 1],
            ['language' => 'en', 'user_data' => ['firstName' => 'Ada']],
            function (string $path): void {
                file_put_contents($path, 'old');
            }
        );

        $previousPath = $profile->fresh()->pdf_path;
        Storage::disk('public')->assertExists($previousPath);

        $this->store->put(
            $profile->fresh(),
            'cvs',
            'cv',
            ['template_id' => 1],
            ['language' => 'en', 'user_data' => ['firstName' => 'Grace']],
            function (string $path): void {
                file_put_contents($path, 'new');
            }
        );

        $fresh = $profile->fresh();

        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists($fresh->pdf_path);
        $this->assertNotSame($previousPath, $fresh->pdf_path);
        $this->assertSame('new', Storage::disk('public')->get($fresh->pdf_path));
        $this->assertStringStartsWith('cvs/'.$profile->id.'-', $fresh->pdf_path);
        $this->assertTrue($fresh->updated_at->equalTo($updatedAt));
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'name' => 'My CV',
            'deleted_at' => null,
        ]);
    }

    public function test_replaces_cover_letter_pdf_and_keeps_the_record(): void
    {
        $letter = CoverLetter::create([
            'name' => 'Letter',
            'language' => 'en',
        ]);

        $this->store->put(
            $letter,
            'cover-letters',
            'cl',
            ['template_id' => 2],
            ['language' => 'en', 'user_data' => ['firstName' => 'Ada']],
            function (string $path): void {
                file_put_contents($path, 'old');
            }
        );

        $previousPath = $letter->fresh()->pdf_path;

        $this->store->put(
            $letter->fresh(),
            'cover-letters',
            'cl',
            ['template_id' => 3],
            ['language' => 'en', 'user_data' => ['firstName' => 'Ada']],
            function (string $path): void {
                file_put_contents($path, 'new');
            }
        );

        $fresh = $letter->fresh();

        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists($fresh->pdf_path);
        $this->assertStringStartsWith('cover-letters/'.$letter->id.'-', $fresh->pdf_path);
        $this->assertDatabaseHas('cover_letters', [
            'id' => $letter->id,
            'name' => 'Letter',
            'deleted_at' => null,
        ]);
    }

    public function test_does_not_delete_files_outside_the_pdf_directory(): void
    {
        $profile = $this->profile();
        Storage::disk('public')->put('cv-photos/keep.png', 'photo');
        $profile->forceFill([
            'pdf_path' => 'cv-photos/keep.png',
            'pdf_fingerprint' => 'stale',
        ])->save();

        $this->store->put(
            $profile->fresh(),
            'cvs',
            'cv',
            ['template_id' => 1],
            ['language' => 'en'],
            function (string $path): void {
                file_put_contents($path, 'pdf');
            }
        );

        Storage::disk('public')->assertExists('cv-photos/keep.png');
        $this->assertStringStartsWith('cvs/', (string) $profile->fresh()->pdf_path);
    }

    public function test_unsaved_document_is_not_inserted(): void
    {
        $profile = new Profile([
            'name' => 'Draft',
            'language' => 'en',
        ]);

        $url = $this->store->put(
            $profile,
            'cvs',
            'cv',
            ['template_id' => 1],
            ['language' => 'en'],
            function (string $path): void {
                file_put_contents($path, 'pdf');
            }
        );

        $this->assertDatabaseCount('profiles', 0);
        $this->assertNotSame('', $url);
        $this->assertCount(1, Storage::disk('public')->allFiles('cvs'));
    }

    private function profile(): Profile
    {
        return Profile::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'My CV',
            'language' => 'en',
        ]);
    }
}
