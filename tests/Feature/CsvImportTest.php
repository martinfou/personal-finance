<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_reads_csv_from_local_disk_path(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        Category::create([
            'user_id' => $user->id,
            'name' => 'Autre',
            'type' => 'expense',
            'icon' => '•',
            'color' => '#6b6358',
        ]);

        $upload = UploadedFile::fake()->createWithContent(
            'cibc.csv',
            "Date,Description,Debit,Credit\n2025-01-10,TEST STORE,25.00,\n",
        );

        $path = Storage::disk('local')->putFile('csv-imports', $upload);

        $response = $this->actingAs($user)->post(route('csv-import.import'), [
            'path' => $path,
            'bank' => 'cibc_visa',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'TEST STORE',
        ]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_import_shows_error_when_stored_file_is_missing(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('csv-import.import'), [
            'path' => 'csv-imports/missing.csv',
            'bank' => 'cibc_visa',
        ]);

        $response->assertRedirect(route('csv-import.index'));
        $response->assertSessionHas('error');
    }
}
