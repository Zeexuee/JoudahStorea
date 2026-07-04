@props(['events' => collect()])

<section class="py-24 bg-gray-50" id="events">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-amber-600 uppercase tracking-widest text-sm font-semibold mb-2 block">Berita & Acara</span>
            <h2 class="text-4xl lg:text-5xl font-serif text-gray-900 leading-tight">Ikuti Perjalanan Kami</h2>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">Dapatkan informasi terbaru mengenai acara, pameran, dan peluncuran produk dari Joudah Store.</p>
        </div>

        @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($events as $event)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition hover:shadow-lg hover:-translate-y-1 duration-300">
                    <a href="{{ route('event.detail', $event->slug) }}" class="block overflow-hidden relative group w-full" style="height: 280px;">
                        @if($event->image)
                            <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover transition hover:scale-105 duration-500">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fa-regular fa-image text-4xl text-gray-400"></i>
                            </div>
                        @endif
                        
                        @if($event->event_date)
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded text-sm font-semibold text-amber-800 shadow-sm">
                            {{ $event->event_date->format('d M Y') }}
                        </div>
                        @endif
                    </a>
                    <div class="p-6 flex flex-col flex-grow">
                        <a href="{{ route('event.detail', $event->slug) }}">
                            <h3 class="text-xl font-bold text-gray-900 mb-3 hover:text-amber-600 transition">{{ $event->title }}</h3>
                        </a>
                        <p class="text-gray-600 text-sm mb-6 line-clamp-3">{{ Str::limit(strip_tags($event->description), 120) }}</p>
                        <div class="mt-auto flex items-center gap-3">
                            <a href="{{ route('event.detail', $event->slug) }}" class="text-amber-600 hover:text-amber-700 font-medium text-sm transition">
                                Baca Detail &rarr;
                            </a>
                            @if($event->external_url)
                                <a href="{{ $event->external_url }}" target="_blank" rel="noopener noreferrer" class="bg-gray-900 text-white hover:bg-gray-800 px-4 py-2 rounded text-xs font-semibold uppercase tracking-wider transition ml-auto">
                                    Go to Event
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-xl border border-gray-100 shadow-sm">
                <i class="fa-regular fa-calendar text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Belum ada acara terbaru saat ini.</p>
            </div>
        @endif
    </div>
</section>
