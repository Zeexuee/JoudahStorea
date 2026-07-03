<x-layouts.app title="{{ $event->title }} - Joudah Store" description="{{ Str::limit(strip_tags($event->description), 150) }}">
    <div class="pt-24 pb-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <a href="{{ url('/') }}#events" class="text-amber-600 hover:text-amber-700 font-medium inline-flex items-center transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
            </div>

            <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Hero Image -->
                <div class="w-full h-auto aspect-video relative bg-gray-100">
                    @if($event->image)
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                            <i class="fa-regular fa-image text-5xl mb-4"></i>
                            <span class="text-sm">Tidak ada gambar</span>
                        </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="p-8 md:p-12">
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                        @if($event->event_date)
                            <div class="flex items-center gap-2 text-amber-700 font-semibold bg-amber-50 px-3 py-1 rounded-full">
                                <i class="fa-regular fa-calendar"></i>
                                <span>{{ $event->event_date->format('d F Y') }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <i class="fa-regular fa-clock"></i>
                            <span>Diposting {{ $event->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <h1 class="text-3xl md:text-5xl font-serif text-gray-900 mb-8 leading-tight">{{ $event->title }}</h1>

                    <div class="prose prose-lg prose-amber max-w-none text-gray-700">
                        {!! $event->description !!}
                    </div>
                </div>
            </article>
        </div>
    </div>
    
    <x-footer />
</x-layouts.app>
