<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Email</title>
</head>
<body style="font-family: 'Instrument Sans', 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; color: #111827;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #f3f4f6; border-radius: 16px; margin: 40px auto; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
        <!-- Header -->
        <tr>
            <td style="padding: 40px 32px 20px; text-align: center; border-bottom: 1px solid #f3f4f6;">
                <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #111827; letter-spacing: -0.5px;">
                    {{ config('app.name') }}
                </h1>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding: 40px 32px; text-align: center;">
                <h2 style="margin: 0 0 16px 0; font-size: 22px; font-weight: 600; color: #111827;">
                    Verifikasi Email
                </h2>
                <p style="font-size: 15px; line-height: 24px; color: #4b5563; margin: 0 0 32px 0;">
                    Halo <strong>{{ $user->name }}</strong>, terima kasih telah mendaftar di {{ config('app.name') }}. Silakan masukkan kode OTP di bawah ini untuk melanjutkan.
                </p>
                
                <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; display: inline-block; margin-bottom: 32px; min-width: 250px;">
                    <p style="margin: 0 0 12px 0; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 1px;">Kode OTP (6 Digit)</p>
                    <span style="font-family: monospace; font-size: 36px; font-weight: 700; letter-spacing: 12px; color: #111827; display: block; padding-left: 12px;">
                        {{ $otpCode }}
                    </span>
                </div>
                
                <p style="font-size: 14px; line-height: 20px; color: #6b7280; margin: 0;">
                    Kode ini berlaku selama 10 menit. Jika Anda tidak merasa mendaftar di situs kami, Anda dapat mengabaikan email ini.
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; border-top: 1px solid #f3f4f6; padding: 24px 32px; text-align: center;">
                <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
