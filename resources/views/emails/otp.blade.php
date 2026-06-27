<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; margin: 40px auto; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <!-- Header -->
        <tr>
            <td style="background-color: #111827; padding: 32px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">
                    {{ config('app.name') }}
                </h1>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding: 40px 32px; text-align: center;">
                <h2 style="color: #1f2937; margin: 0 0 16px 0; font-size: 22px; font-weight: 600;">
                    Verifikasi Alamat Email Anda
                </h2>
                <p style="color: #4b5563; font-size: 16px; line-height: 24px; margin: 0 0 32px 0;">
                    Halo <strong>{{ $user->name }}</strong>, terima kasih telah mendaftar. Gunakan kode verifikasi di bawah ini untuk mengonfirmasi email Anda. Kode ini berlaku selama 10 menit.
                </p>
                <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; display: inline-block; margin-bottom: 32px;">
                    <span style="font-family: monospace; font-size: 36px; font-weight: 700; letter-spacing: 6px; color: #b45309; padding-left: 6px;">
                        {{ $otpCode }}
                    </span>
                </div>
                <p style="color: #9ca3af; font-size: 13px; line-height: 20px; margin: 0;">
                    Jika Anda tidak melakukan pendaftaran ini, harap abaikan email ini.
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; border-top: 1px solid #f3f4f6; padding: 24px 32px; text-align: center; color: #9ca3af; font-size: 12px;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
