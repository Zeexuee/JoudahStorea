<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpVerificationService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly FonteNotificationService $fonte)
    {
    }

    public function sendOtp(User $user, string $channel): array
    {
        if ($channel === 'phone' && $user->phone_verified_at) {
            return [
                'success' => false,
                'message' => 'Nomor telepon sudah terverifikasi.',
            ];
        }

        if ($channel === 'email' && $user->email_verified_at) {
            return [
                'success' => false,
                'message' => 'Email sudah terverifikasi.',
            ];
        }

        $destination = $this->resolveDestination($user, $channel);

        if (!$destination) {
            return [
                'success' => false,
                'message' => 'Nomor telepon belum tersedia pada akun.',
            ];
        }

        $latest = OtpVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->orderByDesc('id')
            ->first();

        if ($latest && $latest->sent_at && $latest->sent_at->gt(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
            $remaining = now()->diffInSeconds($latest->sent_at->copy()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

            return [
                'success' => false,
                'message' => 'Tunggu ' . $remaining . ' detik sebelum kirim ulang OTP.',
            ];
        }

        $code = $this->generateOtp();

        // Save code in session for local development so it can be displayed on screen
        if (app()->environment('local')) {
            session()->put('dev_otp_code', $code);
        }

        $otp = OtpVerification::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'sent_at' => now(),
        ]);

        if ($channel === 'phone') {
            $deliveryResult = $this->fonte->sendOtpToPhone($destination, $code);
        } elseif ($channel === 'email') {
            try {
                \Illuminate\Support\Facades\Mail::to($destination)->send(new \App\Mail\SendOtpMail($user, $code));
                $deliveryResult = ['success' => true];
            } catch (\Exception $e) {
                $deliveryResult = ['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()];
            }
        }

        if (!($deliveryResult['success'] ?? false)) {
            $otp->delete();

            return [
                'success' => false,
                'message' => $deliveryResult['message'] ?? 'Gagal mengirim OTP.',
            ];
        }

        return [
            'success' => true,
            'message' => $channel === 'email' ? 'OTP berhasil dikirim ke email Anda.' : 'OTP berhasil dikirim ke nomor WhatsApp.',
        ];
    }

    public function verifyOtp(User $user, string $channel, string $code): array
    {
        if ($channel === 'phone' && $user->phone_verified_at) {
            return [
                'success' => true,
                'message' => 'Nomor telepon sudah terverifikasi sebelumnya.',
            ];
        }

        if ($channel === 'email' && $user->email_verified_at) {
            return [
                'success' => true,
                'message' => 'Email sudah terverifikasi sebelumnya.',
            ];
        }

        $otp = OtpVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->orderByDesc('id')
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'OTP tidak ditemukan. Silakan kirim OTP terlebih dahulu.',
            ];
        }

        if ($otp->expires_at->isPast()) {
            return [
                'success' => false,
                'message' => 'OTP sudah kedaluwarsa. Silakan kirim OTP baru.',
            ];
        }

        if ((int) $otp->attempts >= self::MAX_ATTEMPTS) {
            return [
                'success' => false,
                'message' => 'Batas percobaan OTP terlampaui. Silakan kirim OTP baru.',
            ];
        }

        if (!Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return [
                'success' => false,
                'message' => 'Kode OTP tidak valid.',
            ];
        }

        $otp->update([
            'verified_at' => now(),
        ]);

        if ($channel === 'phone') {
            $user->update([
                'phone_verified_at' => now(),
            ]);
        } elseif ($channel === 'email') {
            $user->markEmailAsVerified();
        }

        return [
            'success' => true,
            'message' => $channel === 'email' ? 'Email berhasil diverifikasi.' : 'Nomor telepon berhasil diverifikasi.',
        ];
    }

    private function resolveDestination(User $user, string $channel): ?string
    {
        if ($channel === 'phone') {
            return $user->phone;
        }

        if ($channel === 'email') {
            return $user->email;
        }

        return null;
    }

    private function generateOtp(): string
    {
        $min = (int) pow(10, self::OTP_LENGTH - 1);
        $max = (int) pow(10, self::OTP_LENGTH) - 1;

        return (string) random_int($min, $max);
    }
}
