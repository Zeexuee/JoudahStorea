@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-gray-50/50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-md">
        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900 tracking-tight">
                Verifikasi Email Anda
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Kami telah mengirimkan 6 digit kode OTP verifikasi ke email Anda. Silakan masukkan kode tersebut di bawah ini.
            </p>
        </div>


        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-emerald-800 text-sm flex items-start gap-3">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 text-rose-800 text-sm flex items-start gap-3">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="mt-8 space-y-6">
            <!-- Form untuk Verifikasi Kode OTP -->
            <form method="POST" action="{{ route('verification.verify') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode OTP (6 Digit)</label>
                    <input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required 
                           class="w-full text-center tracking-[0.75em] text-2xl font-bold py-3 px-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                           placeholder="000000">
                </div>

                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md">
                    Verifikasi Kode
                </button>
            </form>

            <div class="text-sm text-gray-500 text-center font-normal">
                Belum menerima kode OTP atau kode sudah kedaluwarsa? Klik tombol di bawah ini untuk mengirim ulang.
            </div>

            <!-- Form untuk Kirim Ulang OTP -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2.5 px-4 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 cursor-pointer">
                    Kirim Ulang Kode OTP
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
