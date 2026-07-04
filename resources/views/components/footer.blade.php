<footer class="bg-white border-t border-gray-100 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-1">
                <a href="/" class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center relative overflow-hidden">
                         <img src="{{ asset('images/logos/logo.png') }}" alt="">
                    </div>
                </a>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Joudah Store menawarkan koleksi eksklusif parfum premium, yang dirancang khusus untuk individu yang elegan dan berkelas.
                </p>
            </div>

            <!-- Links Column 1 -->
            <div>
                <h4 class="font-serif text-gray-900 mb-6">Toko</h4>
                <ul class="space-y-4 text-sm text-gray-500">
                    <li><a href="{{ route('category.show', 'j-scent') }}" class="hover:text-amber-600 transition">Semua Parfum</a></li>
                    <li><a href="{{ route('category.show', 'bukhur') }}" class="hover:text-amber-600 transition">Bukhur Terbaik</a></li>
                    <li><a href="{{ url('/#catalog') }}" class="hover:text-amber-600 transition">Baru Datang</a></li>
                    <li><a href="{{ url('/#catalog') }}" class="hover:text-amber-600 transition">Set Eksklusif</a></li>
                </ul>
            </div>

            <!-- Links Column 2 -->
            <div>
                <h4 class="font-serif text-gray-900 mb-6">Dukungan</h4>
                <ul class="space-y-4 text-sm text-gray-500">
                    <li><a href="https://wa.me/087796715916?text=Halo kak, " class="hover:text-amber-600 transition">Hubungi Kami</a></li>
                    <li><a href="{{ route('shipping-policy') }}" class="hover:text-amber-600 transition">Kebijakan Pengiriman</a></li>
                    <li><a href="{{ route('returns-exchanges') }}" class="hover:text-amber-600 transition">Pengembalian & Tukar</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-amber-600 transition">FAQ</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h4 class="font-serif text-gray-900 mb-6">Follow Us</h4>
                <div class="flex space-x-4">
                    <a href="https://www.instagram.com/joudah_official/" class="text-gray-400 hover:text-amber-600 transition">
                        <span class="sr-only">Instagram</span>
                        <i data-feather="instagram"></i>
                    </a>
                    <a href="https://www.linkedin.com/company/joudah-group/" class="text-gray-400 hover:text-amber-600 transition">
                        <span class="sr-only">Linkedin</span>
                        <i data-feather="linkedin"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
