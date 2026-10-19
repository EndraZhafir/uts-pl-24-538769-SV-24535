<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji 1: Request ke endpoint terproteksi tanpa token harus ditolak (401 Unauthorized)
     */
    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/books');

        $response->assertStatus(401);
    }

    /**
     * Uji 2: Pengguna terautentikasi dapat menambah buku baru (201 Created)
     */
    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Solo Leveling Vol. 1',
            'author' => 'Chugong',
            'type' => 'comic',
            'published_year' => 2018,
            'status' => 'reading',
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.title', 'Solo Leveling Vol. 1');

        $this->assertDatabaseHas('books', [
            'title' => 'Solo Leveling Vol. 1',
            'author' => 'Chugong',
        ]);
    }

    /**
     * Uji 3: Validasi wajib gagal jika payload tidak lengkap (422 Unprocessable Entity)
     */
    public function test_validation_fails_when_required_fields_empty(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/books', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'author', 'type', 'published_year', 'status']);
    }

    /**
     * Uji 4: Pengguna terautentikasi dapat membaca, mengubah, dan menghapus buku
     */
    public function test_authenticated_user_can_update_and_delete_book(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $book = Book::create([
            'title' => 'Initial Title',
            'author' => 'Initial Author',
            'type' => 'novel',
            'published_year' => 2020,
            'status' => 'unread',
        ]);

        // Update
        $updatePayload = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'type' => 'novel',
            'published_year' => 2021,
            'status' => 'completed',
        ];

        $updateResponse = $this->putJson("/api/books/{$book->id}", $updatePayload);
        $updateResponse->assertStatus(200)
                       ->assertJsonPath('data.title', 'Updated Title');

        // Delete
        $deleteResponse = $this->deleteJson("/api/books/{$book->id}");
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}