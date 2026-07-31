<div class="relative w-full h-[60vh] md:h-screen overflow-hidden">
    <!-- Background Images (Desktop Carousel) - Only visible on md screens and up -->
    <div class="hidden md:block absolute inset-0 w-full h-full">
        <img src="{{ asset('images/pc.png') }}" alt="Exclusive Perfume 1" class="hero-slide-desktop absolute inset-0 w-full h-full object-cover opacity-100">
        <img src="{{ asset('images/mid1.png') }}" alt="Exclusive Perfume 2" class="hero-slide-desktop absolute inset-0 w-full h-full object-cover opacity-0">
        <img src="{{ asset('images/hero-2.jpg') }}" alt="Exclusive Perfume 3" class="hero-slide-desktop absolute inset-0 w-full h-full object-cover opacity-0">
        <img src="{{ asset('images/hero-3.jpg') }}" alt="Exclusive Perfume 4" class="hero-slide-desktop absolute inset-0 w-full h-full object-cover opacity-0">
        <img src="{{ asset('images/hero-4.jpg') }}" alt="Exclusive Perfume 5" class="hero-slide-desktop absolute inset-0 w-full h-full object-cover opacity-0">
    </div>

    <!-- Background Images (Mobile Carousel) - Only visible on small screens -->
    <!-- TIP: You can replace the src paths here with mobile-specific portrait images (e.g. mobile-hero-1.jpg) -->
    <div class="block md:hidden absolute inset-0 w-full h-full">
        <img src="{{ asset('images/phone.png') }}" alt="Exclusive Perfume 1" class="hero-slide-mobile absolute inset-0 w-full h-full object-cover opacity-100">
        <img src="{{ asset('images/mid1.png') }}" alt="Exclusive Perfume 2" class="hero-slide-mobile absolute inset-0 w-full h-full object-cover opacity-0">
        <img src="{{ asset('images/hero-2.jpg') }}" alt="Exclusive Perfume 3" class="hero-slide-mobile absolute inset-0 w-full h-full object-cover opacity-0">
        <img src="{{ asset('images/hero-3.jpg') }}" alt="Exclusive Perfume 4" class="hero-slide-mobile absolute inset-0 w-full h-full object-cover opacity-0">
        <img src="{{ asset('images/hero-4.jpg') }}" alt="Exclusive Perfume 5" class="hero-slide-mobile absolute inset-0 w-full h-full object-cover opacity-0">
    </div>
    
    <!-- Gradient Overlay for Text Readability -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>

    <!-- Text Content -->
    <div class="absolute bottom-0 left-0 right-0 p-8 pb-12 flex justify-center text-center">
        <div>
            <h2 class="hero-title text-white text-3xl md:text-4xl lg:text-5xl font-serif tracking-wide drop-shadow-lg mb-2">
                Joudah Store
            </h2>
            <p class="hero-subtitle text-gray-200 text-lg font-light tracking-wider uppercase">
                Fragrance & Lifestyle
            </p>
        </div>
    </div>
</div>
