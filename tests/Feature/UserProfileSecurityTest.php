<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}

