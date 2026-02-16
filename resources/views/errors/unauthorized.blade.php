<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <!-- Header Section -->
        <div class="mb-12 text-center">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-lock text-gray-600 text-2xl"></i>
                </div>
            </div>
            <h1 class="text-5xl font-bold text-gray-900 mb-2">403</h1>
            <p class="text-xl text-gray-600 font-medium">Akses Ditolak</p>
        </div>

        <!-- Content Section -->
        <div class="max-w-lg w-full bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="mb-6">
                <p class="text-gray-700 text-center text-base leading-relaxed">
                    {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
                </p>
            </div>

            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <p class="text-gray-600 text-sm text-center">
                    Hanya pengguna dengan akses administrator yang dapat mengakses area ini. Jika Anda merasa ini adalah kesalahan, hubungi administrator sistem.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="/" class="block w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 px-4 rounded-md transition duration-200 text-center">
                    <i class="fas fa-home mr-2"></i>Kembali ke Home
                </a>
                <a href="/profile" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-900 font-medium py-2.5 px-4 rounded-md transition duration-200 text-center border border-gray-300">
                    <i class="fas fa-user mr-2"></i>Ke Profile
                </a>
            </div>
        </div>

        <!-- Footer Help Text -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm">
                Butuh bantuan? <a href="/" class="text-gray-700 hover:text-gray-900 font-medium">Hubungi support</a>
            </p>
        </div>
    </div>
</body>
</html>
