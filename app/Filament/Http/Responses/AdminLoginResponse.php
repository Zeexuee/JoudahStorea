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
        $loginType = Session::get('login_type', 'products');

        $redirectUrl = match ($loginType) {
            'orders' => route('admin.dashboard'),
            default => route('filament.admin.resources.products.index'),
        };
        
        Session::forget('login_type');
        
        return redirect()->to($redirectUrl);
    }
}
