@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white/80 backdrop-blur-xl p-10 rounded-3xl border border-white/40 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300">
        <div>
            <h2 class="mt-2 text-center text-3xl font-bold text-gray-900 tracking-tight">
                Verifikasi Email
            </h2>
            <p class="mt-3 text-center text-sm text-gray-500 leading-relaxed">
                Kami telah mengirimkan 6 digit kode OTP verifikasi ke email Anda. Silakan masukkan kode tersebut di bawah ini untuk melanjutkan.
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50/80 backdrop-blur-sm border border-emerald-100/50 p-4 text-emerald-700 text-sm font-medium text-center">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl bg-rose-50/80 backdrop-blur-sm border border-rose-100/50 p-4 text-rose-700 text-sm font-medium text-center">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-8 space-y-8">
            <!-- Form untuk Verifikasi Kode OTP -->
            <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3 text-center">Kode OTP (6 Digit)</label>
                    <input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required 
                           class="block w-full text-center tracking-[1em] text-3xl font-bold py-4 px-4 bg-gray-50/50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-inner placeholder-gray-300"
                           placeholder="000000">
                </div>

                <button type="submit" 
                        class="w-full flex justify-center py-4 px-4 text-sm font-semibold rounded-2xl text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-900/10 transition-all duration-300 cursor-pointer shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    Verifikasi Kode
                </button>
            </form>

            <div class="pt-6 border-t border-gray-100">
                <div class="text-sm text-gray-500 text-center font-normal mb-4">
                    Belum menerima kode OTP?
                </div>
                
                <!-- Form untuk Kirim Ulang OTP -->
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex justify-center py-3.5 px-4 border-2 border-gray-100 text-sm font-semibold rounded-2xl text-gray-700 bg-transparent hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 transition-all duration-300 cursor-pointer">
                        Kirim Ulang Kode
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
