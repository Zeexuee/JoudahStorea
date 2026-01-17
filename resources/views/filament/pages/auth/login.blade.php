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
                <div class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-lg font-serif italic text-xl font-bold">J</div>
                <span class="font-serif text-xl font-bold tracking-tight text-gray-900 group-hover:text-amber-700 transition">Joudah</span>
            </a>
        </div>

        <div class="max-w-md w-full mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Sign in</h1>
            <p class="text-gray-500 mb-10 text-sm">Please login to continue to your account.</p>

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

            <!-- Social Login Mockup -->
            <div class="mt-10">
                <p class="text-center text-xs text-gray-400 mb-6">Or continue with</p>
                <div class="flex gap-4 justify-center">
                    <button type="button" class="w-14 h-14 bg-white border border-gray-100 rounded-full flex items-center justify-center hover:shadow-md transition text-lg">
                        <i class="fab fa-google text-gray-900"></i>
                    </button>
                    <button type="button" class="w-14 h-14 bg-white border border-gray-100 rounded-full flex items-center justify-center hover:shadow-md transition text-lg">
                        <i class="fab fa-apple text-gray-900"></i>
                    </button>
                    <button type="button" class="w-14 h-14 bg-white border border-gray-100 rounded-full flex items-center justify-center hover:shadow-md transition text-lg">
                        <i class="fab fa-facebook-f text-[#1877F2]"></i>
                    </button>
                </div>
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
            <span class="text-amber-500 font-bold tracking-widest uppercase text-xs mb-4 block">The collection</span>
            <h2 class="text-6xl font-serif font-medium leading-tight mb-6">Welcome to <br/> <span class="text-amber-100 italic">Joudah Store</span></h2>
            <p class="text-gray-400 text-lg font-light leading-relaxed mb-8">
                Experience the essence of luxury through our curated collection of Oud, Bukhur, and premium fragrances.
            </p>
            <div class="h-1 w-20 bg-amber-600"></div>
        </div>

        <!-- Floating Card (Bottom Right) -->
        <div class="absolute bottom-12 right-12 left-24 bg-[#1E1E1E]/90 backdrop-blur-xl p-8 rounded-3xl border border-white/5 shadow-2xl">
            <div class="flex justify-between items-start">
                <div class="flex -space-x-3">
                     <img class="w-10 h-10 rounded-full border-2 border-[#1E1E1E]" src="https://ui-avatars.com/api/?name=Ali&background=random" alt="">
                     <img class="w-10 h-10 rounded-full border-2 border-[#1E1E1E]" src="https://ui-avatars.com/api/?name=Siti&background=random" alt="">
                     <div class="w-10 h-10 rounded-full border-2 border-[#1E1E1E] bg-amber-600 flex items-center justify-center text-xs font-bold text-white">+2k</div>
                </div>
            </div>
        </div>
    </div>
</div>
