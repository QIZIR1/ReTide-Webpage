<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Registrasi ditolak jika menggunakan password lemah.
     * (Kurang dari 8 char, tidak ada kombinasi huruf besar, kecil, angka, atau simbol).
     */
    public function test_user_cannot_register_with_weak_password(): void
    {
        $weakPasswords = [
            'short',
            'password123',
            'PASSWORD123',
            'PasswordTanpaSimbol123',
            'PasswordTanpaAngka!',
        ];

        foreach ($weakPasswords as $password) {
            $response = $this->post('/register', [
                'email' => 'weak_' . uniqid() . '@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ]);

            $response->assertSessionHasErrors('password');
            $this->assertGuest();
        }
    }

    /**
     * Test 2: Registrasi berhasil jika menggunakan password kuat sesuai policy.
     */
    public function test_user_can_register_with_strong_password(): void
    {
        $strongPassword = 'ReTide#Password2026';

        $response = $this->post('/register', [
            'email' => 'stronguser@example.com',
            'password' => $strongPassword,
            'password_confirmation' => $strongPassword,
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'stronguser@example.com',
        ]);
    }

    /**
     * Test 3: Registrasi ditolak jika konfirmasi password tidak cocok.
     */
    public function test_user_cannot_register_if_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register', [
            'email' => 'mismatch@example.com',
            'password' => 'ReTide#Password2026',
            'password_confirmation' => 'BedaPassword#2026',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test 4: Ganti password akun ditolak jika password baru lemah.
     */
    public function test_user_cannot_update_with_weak_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword#123'),
        ]);

        $response = $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'current_password' => 'OldPassword#123',
            'new_password' => 'weak123',
            'new_password_confirmation' => 'weak123',
        ]);

        $response->assertSessionHasErrors('new_password');
    }

    /**
     * Test 5: Ganti password akun berhasil jika password baru kuat.
     */
    public function test_user_can_update_with_strong_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword#123'),
        ]);

        $response = $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'current_password' => 'OldPassword#123',
            'new_password' => 'NewStrongPass#2026',
            'new_password_confirmation' => 'NewStrongPass#2026',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewStrongPass#2026', $user->fresh()->password));
    }

    /**
     * Test 6: Upload avatar ditolak jika format bukan gambar (misal file PDF).
     */
    public function test_user_cannot_upload_non_image_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }

    /**
     * Test 7: Upload avatar ditolak jika ukuran melebihi batas (3MB > 2MB).
     */
    public function test_user_cannot_upload_avatar_exceeding_max_size(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('large_avatar.jpg')->size(3000); // 3000 KB = ~3MB

        $response = $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }

    /**
     * Test 8: Upload avatar berhasil jika gambar valid (JPG/PNG <= 2MB).
     */
    public function test_user_can_upload_valid_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('my_avatar.png', 400, 400)->size(500);

        $response = $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser->avatar);
        Storage::disk('public')->assertExists($freshUser->avatar);
    }

    /**
     * Test 9: Mengunggah avatar baru akan menghapus file avatar lama dari storage.
     */
    public function test_uploading_new_avatar_deletes_old_avatar_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Upload avatar pertama
        $firstAvatar = UploadedFile::fake()->image('first.jpg', 300, 300);
        $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'avatar' => $firstAvatar,
        ]);
        $oldPath = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($oldPath);

        // Upload avatar kedua
        $secondAvatar = UploadedFile::fake()->image('second.png', 300, 300);
        $this->actingAs($user)->put('/account', [
            'username' => $user->name,
            'email' => $user->email,
            'avatar' => $secondAvatar,
        ]);
        $newPath = $user->fresh()->avatar;

        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }

    /**
     * Test 10: User dapat mengekspor riwayat transaksi mereka sendiri ke CSV,
     * dan data transaksi milik user lain TIDAK bocor ke file ekspor (mencegah IDOR).
     */
    public function test_user_can_export_transaction_history_and_no_data_leakage(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Transaksi milik User A
        Payment::create([
            'user_id' => $userA->id,
            'order_id' => 'ORD-USER-A-001',
            'payment_type' => 'marketplace',
            'status' => 'settlement',
            'gross_amount' => 150000.00,
        ]);

        Payment::create([
            'user_id' => $userA->id,
            'order_id' => 'ORD-USER-A-002',
            'payment_type' => 'donation',
            'status' => 'pending',
            'gross_amount' => 50000.00,
        ]);

        // Transaksi milik User B (harus TIDAK ada di file ekspor User A)
        Payment::create([
            'user_id' => $userB->id,
            'order_id' => 'ORD-USER-B-SECRET-999',
            'payment_type' => 'marketplace',
            'status' => 'settlement',
            'gross_amount' => 999999.00,
        ]);

        $response = $this->actingAs($userA)->get('/account/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();

        // Verifikasi header CSV
        $this->assertStringContainsString('order_id,tipe,status,nominal,tanggal', $content);

        // Verifikasi data milik User A ada
        $this->assertStringContainsString('ORD-USER-A-001', $content);
        $this->assertStringContainsString('ORD-USER-A-002', $content);

        // Verifikasi IDOR Prevention: data milik User B sama sekali tidak boleh ada
        $this->assertStringNotContainsString('ORD-USER-B-SECRET-999', $content);
    }

    /**
     * Test 11: User dapat mengekspor riwayat transaksi dalam format JSON jika parameter format=json.
     */
    public function test_user_can_export_transaction_history_as_json(): void
    {
        $user = User::factory()->create();

        Payment::create([
            'user_id' => $user->id,
            'order_id' => 'ORD-JSON-TEST-001',
            'payment_type' => 'donation',
            'status' => 'settlement',
            'gross_amount' => 75000.00,
        ]);

        $response = $this->actingAs($user)->get('/account/export?format=json');

        $response->assertOk();
        $response->assertJsonFragment([
            'order_id' => 'ORD-JSON-TEST-001',
        ]);
    }

    /**
     * Test 12: Pengunjung yang belum login (guest) tidak dapat mengekspor riwayat.
     */
    public function test_guest_cannot_export_transaction_history(): void
    {
        $response = $this->get('/account/export');

        $response->assertRedirect('/login');
    }
}

