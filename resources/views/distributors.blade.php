@php
    $support = config('distributors.support', []);
    $locations = \App\Models\Distributor::all();
    $featuredLocations = $locations->where('featured', true)->values();
    $center = $featuredLocations->first() ?? $locations->first();
    $allLocations = $locations->values();
@endphp

<x-layouts.app title="Distributors - Joudah Store" description="Find nearby Joudah Store distributors, contacts, and store locations.">
    <style>
        .leaflet-container {
            border-radius: 28px;
            border: 1px solid rgba(17, 24, 39, 0.08);
            position: relative !important;
            z-index: 0 !important;
        }

        .leaflet-top,
        .leaflet-bottom {
            z-index: 10 !important;
        }

        .distributor-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .distributor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            border-color: rgba(180, 83, 9, 0.18);
        }

        .distributor-card.hidden-by-filter {
            display: none !important;
        }
    </style>

    <div class="bg-[#f8fbfd] min-h-screen">
        <div class="h-24"></div>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10 pt-8">
            <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-8 items-end">
                <div>
                    <span class="text-amber-600 uppercase tracking-[0.25em] text-xs font-bold mb-4 block">Distributors</span>
                    <h1 class="text-4xl md:text-6xl font-serif text-gray-900 leading-tight mb-5">Temukan toko distributor, kontak, dan lokasi terdekat.</h1>
                    <p class="text-gray-600 text-lg leading-relaxed max-w-3xl">
                        Gunakan peta untuk melihat persebaran mitra, pilih toko di bawah untuk melihat detail kontak, dan buka rute langsung ke lokasi distributor.
                    </p>
                </div>

                <div class="bg-white rounded-[28px] border border-gray-100 shadow-sm p-6 md:p-7">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-3xl font-serif text-gray-900">{{ count($locations) }}</p>
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500 mt-1">Mitra</p>
                        </div>
                        <div>
                            <p class="text-3xl font-serif text-gray-900">{{ count($featuredLocations) }}</p>
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500 mt-1">Utama</p>
                        </div>
                        <div>
                            <p class="text-3xl font-serif text-gray-900">24/7</p>
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500 mt-1">Akses</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="mailto:{{ $support['email'] ?? 'admin@joudahstore.com' }}" class="inline-flex items-center justify-center px-5 py-3 rounded-full bg-gray-900 text-white font-medium hover:bg-amber-600 transition">
                            Hubungi Kami
                        </a>
                        <a href="https://wa.me/{{ $support['whatsapp'] ?? ($support['phone'] ?? '6281234567890') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-5 py-3 rounded-full border border-gray-200 text-gray-900 font-medium hover:border-gray-900 transition">
                            WhatsApp Support
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 pt-8">
            <div class="grid grid-cols-1 gap-8 items-start">
                <div class="space-y-4">
                    <div class="relative z-0 overflow-hidden rounded-[32px] shadow-[0_24px_80px_rgba(15,23,42,0.12)] bg-white">
                        <div id="distributor-map" class="w-full h-[420px] md:h-[560px]"></div>
                        <div class="absolute top-4 left-4 right-4 md:right-auto md:w-[460px] z-10">
                            <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-white/70 shadow-lg px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.25em] text-gray-500 mb-2">Where to buy Joudah</p>
                                <input id="distributor-search" type="text" placeholder="Ketik nama, kota, atau provinsi di sini..." class="w-full rounded-full border border-gray-200 px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 px-1">
                        <div>
                            <h2 class="text-2xl font-serif text-gray-900">Pilih Mitra Terdekat di Kotamu</h2>
                            <p class="text-sm text-gray-500 mt-1">Klik kartu toko untuk menyorot pin pada peta dan membuka rute.</p>
                        </div>
                        <a href="{{ route('distributors.index') }}" class="text-sm font-medium text-amber-700 hover:text-amber-800 transition">Reset tampilan</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="distributor-list">
                        @foreach($allLocations as $location)
                            @php
                                $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . $location['lat'] . ',' . $location['lng'];
                                $waUrl = 'https://wa.me/' . $location['phone'];
                                $searchTerms = strtolower(trim($location['name'] . ' ' . $location['city'] . ' ' . $location['province']));
                            @endphp
                            <article
                                class="distributor-card bg-white border border-gray-100 rounded-3xl p-5 shadow-sm"
                                data-location-card
                                data-search="{{ e($searchTerms) }}"
                                data-lat="{{ $location['lat'] }}"
                                data-lng="{{ $location['lng'] }}"
                                data-name="{{ e($location['name']) }}"
                            >
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-gray-900 uppercase tracking-tight leading-tight">{{ $location['name'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $location['city'] }} - {{ $location['province'] }}</p>
                                    </div>
                                    @if($location['featured'])
                                        <span class="shrink-0 text-[10px] uppercase tracking-[0.25em] bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-bold">Featured</span>
                                    @endif
                                </div>

                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-900 hover:border-emerald-500 hover:text-emerald-700 transition mb-4">
                                    <i class="fa-brands fa-whatsapp text-lg text-emerald-600"></i>
                                    {{ $location['phone'] }}
                                </a>

                                <p class="text-sm leading-6 text-gray-600 mb-5">{{ $location['address'] }}</p>

                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="button" class="select-location inline-flex items-center justify-center px-4 py-3 rounded-full bg-gray-900 text-white text-sm font-medium hover:bg-amber-600 transition" data-target-lat="{{ $location['lat'] }}" data-target-lng="{{ $location['lng'] }}">Authorized</button>
                                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-4 py-3 rounded-full border border-gray-200 bg-white text-gray-900 text-sm font-medium hover:border-gray-900 transition">Go to Store</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const locations = @json($allLocations);
                const defaultLocation = @json($center);
                const map = L.map('distributor-map', {
                    scrollWheelZoom: false,
                    zoomControl: true,
                }).setView([defaultLocation?.lat ?? -2.5, defaultLocation?.lng ?? 118], 5);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const markers = [];
                const markerIcon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41],
                });

                locations.forEach((location) => {
                    const marker = L.marker([location.lat, location.lng], { icon: markerIcon }).addTo(map);
                    marker.bindPopup(`<strong>${location.name}</strong><br>${location.city} - ${location.province}`);
                    marker.locationName = location.name;
                    marker.locationLat = location.lat;
                    marker.locationLng = location.lng;
                    markers.push(marker);
                });

                const cards = Array.from(document.querySelectorAll('[data-location-card]'));
                const searchInput = document.getElementById('distributor-search');

                function focusLocation(lat, lng, zoom = 12) {
                    map.setView([lat, lng], zoom, { animate: true });
                    const marker = markers.find((item) => Number(item.locationLat) === Number(lat) && Number(item.locationLng) === Number(lng));
                    if (marker) {
                        marker.openPopup();
                    }
                }

                document.querySelectorAll('[data-target-lat]').forEach((button) => {
                    button.addEventListener('click', function () {
                        focusLocation(this.dataset.targetLat, this.dataset.targetLng);
                    });
                });

                cards.forEach((card) => {
                    card.addEventListener('click', function (event) {
                        if (event.target.closest('a') || event.target.closest('button')) {
                            return;
                        }

                        focusLocation(this.dataset.lat, this.dataset.lng);
                    });
                });

                searchInput.addEventListener('input', function () {
                    const term = this.value.trim().toLowerCase();

                    cards.forEach((card) => {
                        const match = card.dataset.search.includes(term);
                        card.classList.toggle('hidden-by-filter', !match);
                    });
                });

                const firstMarker = markers[0];
                if (firstMarker) {
                    firstMarker.openPopup();
                }
            });
        </script>
    @endpush
</x-layouts.app>