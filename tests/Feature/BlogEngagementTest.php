<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogEngagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: membuat artikel blog dengan isi tertentu.
     */
    private function makePost(string $content, string $title = 'Artikel Uji Waktu Baca'): BlogPost
    {
        return BlogPost::create([
            'title' => $title,
            'content' => $content,
        ]);
    }

    /**
     * Helper: menghasilkan teks berisi tepat $n kata.
     */
    private function words(int $n): string
    {
        return implode(' ', array_fill(0, $n, 'laut'));
    }

    public function test_blog_post_calculates_correct_estimated_reading_time(): void
    {
        // Arrange: artikel 400 kata
        $post = $this->makePost($this->words(400));

        // Act
        $readingTime = $post->reading_time;

        // Assert: 400 / 200 = 2 menit
        $this->assertSame(2, $readingTime);
    }

    public function test_reading_time_is_rounded_up_to_next_minute(): void
    {
        // Arrange: artikel 401 kata (sedikit lebih dari 2 menit)
        $post = $this->makePost($this->words(401));

        // Act
        $readingTime = $post->reading_time;

        // Assert: 401 / 200 = 2.005 dibulatkan ke atas menjadi 3
        $this->assertSame(3, $readingTime);
    }

    public function test_reading_time_minimum_is_one_minute_for_short_post(): void
    {
        // Arrange: artikel sangat pendek
        $post = $this->makePost('Halo laut');

        // Act & Assert: teks pendek, kosong, dan null tetap minimal 1 menit
        $this->assertSame(1, $post->reading_time);
        $this->assertSame(1, BlogPost::calculateReadingTime(''));
        $this->assertSame(1, BlogPost::calculateReadingTime(null));
    }

    public function test_reading_time_ignores_html_tags(): void
    {
        // Arrange: 200 kata dibungkus tag HTML, ditambah 300 tag <br> yang bukan kata
        $content = '<p><strong>'.$this->words(200).'</strong></p>'.str_repeat('<br>', 300);
        $post = $this->makePost($content);

        // Act
        $readingTime = $post->reading_time;

        // Assert: tag HTML tidak dihitung sebagai kata, jadi tetap 1 menit
        $this->assertSame(1, $readingTime);
    }

    public function test_reading_time_is_displayed_on_blog_pages(): void
    {
        // Arrange: user biasa dan artikel 600 kata (3 menit)
        $user = User::factory()->create(['role' => 'user']);
        $post = $this->makePost($this->words(600));

        // Act
        $indexResponse = $this->actingAs($user)->get('/blog');
        $showResponse = $this->actingAs($user)->get("/blog/{$post->id}");

        // Assert: estimasi waktu baca tampil di halaman daftar dan detail
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('3 min read');
        $showResponse->assertStatus(200);
        $showResponse->assertSee('3 min read');
    }

    // =========================================================================
    // Fitur 2: Bookmark Artikel
    // =========================================================================

    public function test_user_can_bookmark_and_unbookmark_blog_post(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => 'user']);
        $post = $this->makePost($this->words(50));

        // Act: simpan artikel
        $saveResponse = $this->actingAs($user)
            ->from("/blog/{$post->id}")
            ->post(route('blog.bookmark', $post->id));

        // Assert: tersimpan di database dan muncul pesan sukses
        $saveResponse->assertRedirect("/blog/{$post->id}");
        $saveResponse->assertSessionHas('success', 'Artikel disimpan ke bookmark.');
        $this->assertDatabaseHas('blog_bookmarks', [
            'user_id' => $user->id,
            'blog_post_id' => $post->id,
        ]);

        // Act: klik lagi untuk menghapus bookmark
        $removeResponse = $this->actingAs($user)
            ->from("/blog/{$post->id}")
            ->post(route('blog.bookmark', $post->id));

        // Assert: bookmark terhapus
        $removeResponse->assertSessionHas('success', 'Artikel dihapus dari bookmark.');
        $this->assertDatabaseMissing('blog_bookmarks', [
            'user_id' => $user->id,
            'blog_post_id' => $post->id,
        ]);
    }

    public function test_guest_cannot_bookmark_post(): void
    {
        // Arrange: tamu (belum login)
        $post = $this->makePost($this->words(50));

        // Act
        $response = $this->post(route('blog.bookmark', $post->id));

        // Assert: diarahkan ke login dan tidak ada bookmark yang dibuat
        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('blog_bookmarks', 0);
    }

    public function test_bookmarking_nonexistent_post_returns_404(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => 'user']);

        // Act: artikel dengan id 99999 tidak ada
        $response = $this->actingAs($user)->post('/blog/99999/bookmark');

        // Assert
        $response->assertStatus(404);
        $this->assertDatabaseCount('blog_bookmarks', 0);
    }

    public function test_saved_filter_only_shows_current_users_bookmarks(): void
    {
        // Arrange: dua user, masing-masing menyimpan artikel berbeda
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $myPost = $this->makePost($this->words(50), 'Artikel Simpanan Saya');
        $otherPost = $this->makePost($this->words(50), 'Artikel Simpanan Orang Lain');
        $this->makePost($this->words(50), 'Artikel Tidak Disimpan');

        $user->bookmarkedPosts()->attach($myPost->id);
        $otherUser->bookmarkedPosts()->attach($otherPost->id);

        // Act
        $response = $this->actingAs($user)->get('/blog?saved=1');

        // Assert: hanya artikel milik user yang login yang tampil
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('blog'));
        $response->assertSee('Artikel Simpanan Saya');
        $response->assertDontSee('Artikel Simpanan Orang Lain');
        $response->assertDontSee('Artikel Tidak Disimpan');
    }

    public function test_show_page_reflects_bookmark_status(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => 'user']);
        $post = $this->makePost($this->words(50));

        // Act & Assert: sebelum disimpan, tombol "Simpan Artikel"
        $this->actingAs($user)->get("/blog/{$post->id}")
            ->assertStatus(200)
            ->assertSee('Simpan Artikel')
            ->assertDontSee('Tersimpan');

        // Act: simpan artikel
        $user->bookmarkedPosts()->attach($post->id);

        // Assert: setelah disimpan, tombol berubah menjadi "Tersimpan"
        $this->actingAs($user)->get("/blog/{$post->id}")
            ->assertStatus(200)
            ->assertSee('Tersimpan')
            ->assertDontSee('Simpan Artikel');
    }
}
