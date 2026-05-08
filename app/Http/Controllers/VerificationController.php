<?php

namespace App\Http\Controllers;

use App\Services\OtpVerificationService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function sendOtp(Request $request, OtpVerificationService $otpService)
    {
        $validated = $request->validate([
            'channel' => 'required|in:phone',
        ]);

        $result = $otpService->sendOtp($request->user(), $validated['channel']);

        return redirect()->route('profile.show')
            ->with(($result['success'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Gagal memproses OTP.');
    }

    public function verifyOtp(Request $request, OtpVerificationService $otpService)
    {
        $validated = $request->validate([
            'channel' => 'required|in:phone',
            'otp_code' => 'required|digits:6',
        ]);

        $result = $otpService->verifyOtp($request->user(), $validated['channel'], $validated['otp_code']);

        return redirect()->route('profile.show')
            ->with(($result['success'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Gagal verifikasi OTP.');
    }
}
