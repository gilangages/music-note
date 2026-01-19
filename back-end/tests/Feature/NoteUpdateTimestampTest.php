<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteUpdateTimestampTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_note_updates_the_updated_at_timestamp()
    {
        $user = User::factory()->create();
        // Factory sudah benar menggunakan music_track_id, aman.
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'content' => 'Konten awal',
        ]);

        $createdAt = $note->created_at;
        sleep(1); // Jeda agar timestamp berbeda

        // UPDATE: Gunakan key 'music_' sesuai UpdateNoteRequest
        $response = $this->actingAs($user)
            ->putJson("/api/notes/{$note->id}", [
                'content' => 'Konten setelah diedit',
                'recipient' => $note->recipient,
                'initial_name' => $note->initial_name,

                // GANTI DARI spotify_ JADI music_
                'music_track_id' => $note->music_track_id,
                'music_track_name' => $note->music_track_name,
                'music_artist_name' => $note->music_artist_name,
                'music_album_image' => $note->music_album_image,
                'music_preview_url' => $note->music_preview_url,
                'music_track_link' => $note->music_track_link,
            ]);

        $response->assertStatus(200);

        $updatedNote = $note->fresh();
        $this->assertTrue($updatedNote->updated_at->gt($createdAt));
    }
}
