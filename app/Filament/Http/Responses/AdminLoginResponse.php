<?php

namespace App\Filament\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * Custom Login Response yang mengarahkan admin ke URL yang sesuai
 * berdasarkan login_type yang disimpan di session
 */
class AdminLoginResponse implements Responsable
{
    /**
     * Tentukan URL redirect berdasarkan login type
     */
    public function toResponse($request): RedirectResponse | Redirector
    {
        // Ambil login type dari session
        $loginType = Session::get('login_type', 'products');
        
        // Tentukan redirect URL berdasarkan login type
        if ($loginType === 'orders') {
            // Kelola Pesanan → /admin/dashboard
            $redirectUrl = '/admin/dashboard';
        } else {
            // Kelola Barang → /admin
            $redirectUrl = '/admin';
        }
        
        // Clear session agar tidak mempengaruhi login berikutnya
        Session::forget('login_type');
        
        // Return redirect ke URL yang sesuai
        return redirect()->intended($redirectUrl);
    }
}
