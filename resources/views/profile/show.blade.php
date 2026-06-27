@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pt-20 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Profil Saya</h1>
            <p class="text-gray-600">Kelola informasi akun dan data pribadi Anda</p>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-24">
                    <nav class="flex flex-col">
                        <a href="{{ route('profile.show') }}" class="px-6 py-4 border-l-4 border-amber-600 bg-amber-50 text-amber-700 font-medium flex items-center gap-3">
                            <i class="fas fa-user text-amber-600"></i>
                            Profil Saya
                        </a>
                        <a href="{{ route('orders.index') }}" class="px-6 py-4 border-l-4 border-transparent text-gray-700 hover:bg-gray-50 hover:border-amber-300 flex items-center gap-3 transition">
                            <i class="fas fa-shopping-bag text-gray-400"></i>
                            Riwayat Pesanan
                        </a>
                        <form method="POST" action="{{ route('auth.logout') }}" class="border-t">
                            @csrf
                            <button type="submit" class="w-full text-left px-6 py-4 text-red-600 hover:bg-red-50 flex items-center gap-3 transition">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <div class="flex">
                            <button type="button" onclick="switchTab('personal')" class="tab-btn active px-6 py-4 border-b-2 border-amber-600 text-amber-600 font-medium">
                                Data Pribadi
                            </button>
                            <button type="button" onclick="switchTab('address')" class="tab-btn px-6 py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900">
                                Alamat Pengiriman
                            </button>
                        </div>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('profile.update') }}" class="p-6">
                        @csrf
                        @method('PUT')

                        <!-- Personal Data Tab -->
                        <div id="personal-tab" class="tab-content">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Lengkap
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Masukkan nama lengkap">
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="sm:col-span-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email
                                    </label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Masukkan email">
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="sm:col-span-2">
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nomor Telepon
                                    </label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Contoh: 08123456789">
                                    @error('phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror


                                </div>
                            </div>
                        </div>

                        <!-- Address Tab -->
                        <div id="address-tab" class="tab-content hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Address -->
                                <div class="sm:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                        Alamat Lengkap
                                    </label>
                                    <textarea id="address" name="address" rows="4" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Jl. Contoh No. 123, Blok A">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        Kota
                                    </label>
                                    <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Contoh: Jakarta">
                                    @error('city')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Province -->
                                <div>
                                    <label for="province" class="block text-sm font-medium text-gray-700 mb-2">
                                        Provinsi
                                    </label>
                                    <input type="text" id="province" name="province" value="{{ old('province', $user->province) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Contoh: DKI Jakarta">
                                    @error('province')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Postal Code -->
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Kode Pos
                                    </label>
                                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Contoh: 12345">
                                    @error('postal_code')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex gap-4">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-6 rounded-lg transition">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                            <a href="{{ route('orders.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition">
                                <i class="fas fa-arrow-right mr-2"></i>Lihat Pesanan
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active state from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-amber-600', 'text-amber-600');
        btn.classList.add('border-transparent', 'text-gray-600');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active state to clicked button
    event.target.classList.remove('border-transparent', 'text-gray-600');
    event.target.classList.add('border-amber-600', 'text-amber-600');
}
</script>
@endsection
