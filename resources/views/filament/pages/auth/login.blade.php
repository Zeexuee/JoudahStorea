@push('styles')
    @vite('resources/css/app.css')
@endpush

<div class="h-screen w-full flex overflow-hidden font-sans">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap');
        .font-serif { font-family: 'Playfair Display', serif; }
        .font-sans { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>

    <!-- LEFT SIDE: Form (40%) -->
    <div class="w-full lg:w-[45%] h-full bg-[#FAFAFA] flex flex-col justify-center px-12 lg:px-24 relative">
        
        <!-- Logo Top Left -->
        <div class="absolute top-8 left-8 lg:left-12">
             <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logos/logo.png') }}" alt="" class="w-10 h-10">
                <span class="font-serif text-xl font-bold tracking-tight text-gray-900 group-hover:text-amber-700 transition">Joudah</span>
            </a>
        </div>

        <div class="max-w-md w-full mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Sign in</h1>
            <p class="text-gray-500 mb-6 text-sm">Please login to continue to your account.</p>

            <!-- Login Type Tabs -->
            <div class="flex gap-4 mb-8 bg-gray-100 p-1 rounded-lg">
                <button type="button" 
                    class="login-tab flex-1 py-2 px-3 rounded-md font-bold text-sm transition @if($loginType === 'products') text-white bg-[#1A1A1A] @else text-gray-600 bg-transparent @endif"
                    wire:click="setLoginType('products')">
                    <i class="fas fa-box mr-2"></i>Kelola Barang
                </button>
                <button type="button" 
                    class="login-tab flex-1 py-2 px-3 rounded-md font-bold text-sm transition @if($loginType === 'orders') text-white bg-[#1A1A1A] @else text-gray-600 bg-transparent @endif"
                    wire:click="setLoginType('orders')">
                    <i class="fas fa-shopping-bag mr-2"></i>Kelola Pesanan
                </button>
            </div>

            <form wire:submit="authenticate" class="space-y-6">
                
                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="far fa-envelope"></i>
                        </span>
                        <input type="email" wire:model="data.email" class="w-full pl-11 pr-4 py-4 bg-white text-gray-900 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition shadow-sm placeholder-gray-400 @error('data.email') border-red-500 @enderror" placeholder="admin@joudahstore.com">
                    </div>
                    @error('data.email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Password Field -->
                 <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" wire:model="data.password" class="w-full pl-11 pr-4 py-4 bg-white text-gray-900 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition shadow-sm placeholder-gray-400 @error('data.password') border-red-500 @enderror" placeholder="••••••••">
                    </div>
                    @error('data.password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="data.remember" class="w-4 h-4 rounded border-gray-300 text-black focus:ring-black">
                        <span class="text-sm font-medium text-gray-600">Remember me</span>
                    </label>
                    <a href="#" class="text-sm font-bold text-gray-900 hover:underline">Forgot Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[#1A1A1A] text-white font-bold py-4 rounded-xl text-sm hover:bg-black transition shadow-lg transform hover:-translate-y-0.5 mt-4">
                    Sign In
                </button>
            </form>

            <div class="mt-4 text-center">
                 @if($errors->any())
                 <div class="text-red-500 text-sm">
                     {{ $errors->first() }}
                 </div>
                 @endif
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: Visual (55%) -->
    <div class="hidden lg:block lg:w-[55%] bg-[#0A0A0A] relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1615634260167-c8cdede054de?q=80&w=2835&auto=format&fit=crop')] bg-cover bg-center opacity-40 mix-blend-overlay"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/50 to-transparent"></div>
        
        <!-- Abstract Shape (CSS Only) -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-900/20 rounded-full blur-[120px]"></div>

        <!-- Content Center -->
        <div class="absolute top-1/2 left-12 right-12 -translate-y-[60%] text-white max-w-lg">
            <span class="text-amber-500 font-bold tracking-widest uppercase text-xs mb-4 block"></span>
            <h2 class="text-6xl font-serif font-medium leading-tight mb-6">Selamat Datang<br/> <span class="text-amber-100 italic">Admin Joudah Store</span></h2>
        </div>
    </div>
</div>
