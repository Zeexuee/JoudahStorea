<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
        $destination = $this->resolveDestination($user, $channel);

        if (!$destination) {
            return [
                'success' => false,
                'message' => $channel === 'email'
                    ? 'Email belum tersedia pada akun.'
                    : 'Nomor telepon belum tersedia pada akun.',
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

        $otp = OtpVerification::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'sent_at' => now(),
        ]);

        $deliveryResult = $channel === 'email'
            ? $this->sendEmailOtp($user, $code)
            : $this->fonte->sendOtpToPhone($destination, $code);

        if (!($deliveryResult['success'] ?? false)) {
            $otp->delete();

            return [
                'success' => false,
                'message' => $deliveryResult['message'] ?? 'Gagal mengirim OTP.',
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP berhasil dikirim ke ' . ($channel === 'email' ? 'email' : 'nomor WhatsApp') . '.',
        ];
    }

    public function verifyOtp(User $user, string $channel, string $code): array
    {
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

        if ($channel === 'email') {
            $user->update([
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'phone_verified_at' => now(),
            ]);
        }

        return [
            'success' => true,
            'message' => ucfirst($channel) . ' berhasil diverifikasi.',
        ];
    }

    private function resolveDestination(User $user, string $channel): ?string
    {
        if ($channel === 'email') {
            return $user->email;
        }

        if ($channel === 'phone') {
            return $user->phone;
        }

        return null;
    }

    private function sendEmailOtp(User $user, string $code): array
    {
        if (!$user->email) {
            return [
                'success' => false,
                'message' => 'Email tidak ditemukan.',
            ];
        }

        try {
            $subject = 'Kode OTP Verifikasi Akun';
            $lines = [
                'Halo ' . ($user->name ?: 'Pelanggan') . ',',
                '',
                'Kode OTP verifikasi Anda adalah: ' . $code,
                'Kode berlaku selama ' . self::OTP_EXPIRY_MINUTES . ' menit.',
                '',
                'Jangan bagikan kode ini ke siapa pun.',
            ];

            Mail::raw(implode("\n", $lines), function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject);
            });

            return ['success' => true];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function generateOtp(): string
    {
        $min = (int) pow(10, self::OTP_LENGTH - 1);
        $max = (int) pow(10, self::OTP_LENGTH) - 1;

        return (string) random_int($min, $max);
    }
}
