<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Http\Responses\AdminLoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Session;

class Login extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.pages.auth.login';
    
    public ?string $loginType = 'products';

    /**
     * Set login type method untuk Livewire
     * Dipanggil ketika user klik tombol "Kelola Barang" atau "Kelola Pesanan"
     */
    public function setLoginType($type): void
    {
        $this->loginType = $type;
        Session::put('login_type', $type);
    }

    /**
     * Override authenticate method dari parent
     * Untuk menggunakan custom AdminLoginResponse yang handle redirect berdasarkan login_type
     * Dan validate bahwa user adalah admin
     */
    public function authenticate(): ?LoginResponse
    {
        // Panggil parent authenticate untuk handle semua logic autentikasi
        // (validasi, check MFA, dll)
        $parentResult = parent::authenticate();
        
        // Jika parent authenticate berhasil (return LoginResponse bukan null)
        if ($parentResult !== null) {
            // Verify bahwa user yang login adalah admin
            $user = auth()->user();
            if (!$user || !$user->is_admin) {
                // Logout user yang bukan admin
                auth()->logout();
                
                // Throw validation exception
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Akses di tolak. Hanya admin yang dapat masuk ke panel admin.',
                ]);
            }
            
            // Simpan login type ke session setelah validasi berhasil
            Session::put('login_type', $this->loginType ?? 'products');
            
            // Return custom AdminLoginResponse untuk redirect ke URL yang sesuai
            return new AdminLoginResponse();
        }
        
        // Jika parent return null (e.g., MFA challenge), pass-through
        return $parentResult;
    }
}

