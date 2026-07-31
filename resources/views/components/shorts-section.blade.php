@props(['videos' => []])

@php
    $videos = collect($videos)->map(function ($video) {
        $video->resolved_video_url = $video->video_path 
            ? (filter_var($video->video_path, FILTER_VALIDATE_URL) ? $video->video_path : asset('storage/' . $video->video_path)) 
            : $video->external_video_url;
        
        $video->resolved_thumbnail_url = $video->thumbnail_path 
            ? (filter_var($video->thumbnail_path, FILTER_VALIDATE_URL) ? $video->thumbnail_path : asset('storage/' . $video->thumbnail_path)) 
            : ($video->thumbnail_url ?? 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&q=80&w=600');
        return $video;
    });
@endphp

@if(count($videos) > 0)
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @media (max-width: 767px) {
            .shorts-mobile-player {
                height: 100dvh !important;
            }
        }
    </style>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="mb-10 text-center md:text-left">
            <span class="text-amber-600 uppercase tracking-[0.25em] text-xs font-bold mb-3 block">Joudah Stories</span>
            <h2 class="text-3xl md:text-5xl font-serif text-gray-900 leading-tight">Review & Cerita Kami</h2>
            <p class="text-gray-500 text-sm md:text-base mt-2 max-w-xl">Tonton video pendek tentang produk, tips, dan kemewahan aroma khas Joudah Store.</p>
        </div>

        <!-- Horizontal Scrollable Container on Mobile, Grid on Desktop -->
        <div class="flex overflow-x-auto pb-4 gap-6 scrollbar-hide md:grid md:grid-cols-3 xl:grid-cols-4 md:overflow-visible">
            @foreach($videos as $video)
                @php
                    $videoUrl = $video->resolved_video_url;
                    $thumbnailUrl = $video->resolved_thumbnail_url;
                    
                    $platformIcon = match($video->social_media_platform) {
                        'instagram' => 'fa-instagram',
                        'tiktok' => 'fa-tiktok',
                        'youtube' => 'fa-youtube',
                        'whatsapp' => 'fa-whatsapp',
                        'shopee' => 'fa-bag-shopping',
                        default => 'fa-link'
                    };
                    $platformColor = match($video->social_media_platform) {
                        'instagram' => 'bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600 text-white',
                        'tiktok' => 'bg-black text-white border border-gray-700',
                        'youtube' => 'bg-red-600 text-white',
                        'whatsapp' => 'bg-emerald-500 text-white',
                        'shopee' => 'bg-orange-500 text-white',
                        default => 'bg-amber-600 text-white'
                    };
                @endphp
                
                <div 
                    class="shrink-0 w-64 md:w-full overflow-hidden relative cursor-pointer group shadow-sm bg-gray-900 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                    style="aspect-ratio: 9 / 16; border-radius: 24px;"
                    onclick="openShortsModal({{ json_encode($videos) }}, {{ $loop->index }})"
                >
                    <!-- Background Cover Image -->
                    <img src="{{ $thumbnailUrl }}" alt="{{ $video->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 opacity-80 group-hover:opacity-90">
                    
                    <!-- Dark Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    
                    <!-- Hover Play Button Icon -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="w-14 h-14 rounded-full bg-white/30 backdrop-blur-md flex items-center justify-center text-white scale-90 group-hover:scale-100 transition-transform duration-300">
                            <i class="fa-solid fa-play text-xl ml-1"></i>
                        </div>
                    </div>

                    <!-- Direct Social Action Button on Card (Bottom Right) -->
                    <a 
                        href="{{ $video->social_media_url }}" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="absolute bottom-4 right-4 z-10 w-10 h-10 rounded-full {{ $platformColor }} flex items-center justify-center shadow-lg transition hover:scale-110"
                        onclick="event.stopPropagation();"
                    >
                        <i class="fa-brands {{ $platformIcon }} text-lg"></i>
                    </a>

                    <!-- Video Title / Caption on Card -->
                    <div class="absolute bottom-4 left-4 right-16 text-white">
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-sm text-[10px] font-semibold tracking-wider uppercase mb-2">
                            {{ $video->social_media_platform }}
                        </span>
                        <h3 class="text-sm font-bold line-clamp-2 leading-snug drop-shadow-md">{{ $video->title }}</h3>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Fullscreen Immersive Shorts Modal -->
    <div id="shorts-modal" class="fixed inset-0 bg-black/95 backdrop-blur-lg hidden items-center justify-center overflow-hidden transition-opacity duration-300 opacity-0 shorts-mobile-player" style="z-index: 999999; background-color: rgba(0,0,0,0.95);">

        <!-- Main Container -->
        <div id="shorts-container" class="relative w-full h-full max-w-md md:h-[90vh] md:aspect-[9/16] md:rounded-[28px] md:overflow-hidden md:shadow-[0_24px_80px_rgba(0,0,0,0.8)] border border-white/5 flex bg-black shorts-mobile-player">
            <!-- Close Button (Inside Main Container so it's always positioned relative to the player) -->
            <button onclick="closeShortsModal()" class="flex items-center justify-center text-white hover:bg-white/10 transition" style="position: absolute; top: 16px; right: 16px; z-index: 50; width: 40px; height: 40px; border-radius: 9999px; background-color: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.1);">
                <i class="fa-solid fa-xmark" style="font-size: 18px;"></i>
            </button>
            
            <!-- Video Element -->
            <video 
                id="shorts-player" 
                class="w-full h-full object-cover cursor-pointer"
                loop
                muted
                playsinline
                webkit-playsinline
                onclick="togglePlayPause()"
            ></video>

            <!-- Play/Pause Flash Overlay Indicator -->
            <div id="player-indicator" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-0 transition-opacity duration-200">
                <div class="w-16 h-16 rounded-full bg-black/50 backdrop-blur-sm flex items-center justify-center text-white text-2xl">
                    <i id="indicator-icon" class="fa-solid fa-play"></i>
                </div>
            </div>

            <!-- Dark Overlay (Bottom) for Text Contrast -->
            <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-black/90 via-black/40 to-transparent pointer-events-none"></div>

            <!-- Left Details Overlay (Bottom Left) -->
            <div class="absolute bottom-6 left-5 text-white flex flex-col pointer-events-auto" style="position: absolute; bottom: 24px; left: 20px; z-index: 30; display: flex; flex-direction: column; width: calc(100% - 80px); max-height: 35vh;">
                <h3 id="shorts-title" class="text-sm font-bold mb-1 drop-shadow-md"></h3>
                <div id="shorts-desc-container" class="overflow-hidden scrollbar-hide max-h-[4.5em] transition-all duration-300 ease-in-out">
                    <p id="shorts-desc" class="text-xs text-gray-200 leading-relaxed drop-shadow-sm font-light line-clamp-3"></p>
                </div>
                <button id="shorts-more-btn" class="text-left text-xs text-amber-400 font-semibold mt-1 hidden" onclick="toggleFullDescription(event)">selengkapnya</button>
            </div>

            <!-- Right Controls Panel Overlay (Floats on the Right) -->
            <div class="absolute right-4 bottom-24 flex flex-col items-center gap-5 text-white" style="position: absolute; right: 16px; bottom: 96px; z-index: 30; display: flex; flex-direction: column; align-items: center;">
                <!-- Direct Social Action Button (Pulsing Highlighted Button) -->
                <div class="flex flex-col items-center">
                    <a 
                        id="shorts-social-btn"
                        href="#" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="transition-transform duration-300 hover:scale-110 active:scale-95 animate-pulse"
                        style="width: 48px; height: 48px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); position: relative; z-index: 30;"
                    >
                        <i id="shorts-social-icon" class="fa-brands" style="font-size: 20px;"></i>
                        <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5" style="width: 10px; height: 10px; display: flex; position: absolute; top: -4px; right: -4px; z-index: 40;">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75" style="position: absolute; width: 100%; height: 100%; border-radius: 9999px; background-color: #ffffff; opacity: 0.75;"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white" style="position: relative; width: 10px; height: 10px; border-radius: 9999px; background-color: #ffffff;"></span>
                        </span>
                    </a>
                </div>
            </div>

            <!-- Next / Prev Slide Arrow Buttons for Desktop Navigation -->
            <button onclick="prevShort()" class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/20 border border-white/5 items-center justify-center text-white opacity-25 hover:opacity-90 hover:bg-black/40 transition duration-300 active:scale-90">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button onclick="nextShort()" class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/20 border border-white/5 items-center justify-center text-white opacity-25 hover:opacity-90 hover:bg-black/40 transition duration-300 active:scale-90">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentVideos = [];
        let currentVideoIndex = 0;
        let isMuted = true;

        const modal = document.getElementById('shorts-modal');
        const player = document.getElementById('shorts-player');
        const titleEl = document.getElementById('shorts-title');
        const descEl = document.getElementById('shorts-desc');
        
        const socialBtn = document.getElementById('shorts-social-btn');
        const socialIcon = document.getElementById('shorts-social-icon');

        const indicator = document.getElementById('player-indicator');
        const indicatorIcon = document.getElementById('indicator-icon');

        function openShortsModal(videos, index) {
            currentVideos = videos;
            currentVideoIndex = index;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.add('opacity-100');
            }, 10);
            
            loadVideo(currentVideoIndex);
            
            // Prevent scrolling on body
            document.body.classList.add('overflow-hidden');
        }

        function closeShortsModal() {
            player.pause();
            player.src = "";
            modal.classList.remove('opacity-100');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
            
            document.body.classList.remove('overflow-hidden');
        }

        function loadVideo(index) {
            const video = currentVideos[index];
            if (!video) return;

            // Load Video Source
            player.src = video.resolved_video_url;
            player.load();
            
            // Text Overlays
            titleEl.textContent = video.title;
            
            // Reset description collapse state
            descEl.textContent = video.description || '';
            descEl.className = 'text-xs text-gray-200 leading-relaxed line-clamp-3 drop-shadow-sm font-light';
            
            const moreBtn = document.getElementById('shorts-more-btn');
            moreBtn.textContent = 'selengkapnya';
            moreBtn.classList.add('hidden');
            
            const descContainer = document.getElementById('shorts-desc-container');
            descContainer.className = 'overflow-hidden scrollbar-hide max-h-[4.5em] transition-all duration-300 ease-in-out';
            descContainer.scrollTop = 0;

            // Wait a moment for rendering and check if the description height exceeds container/line-clamp
            setTimeout(() => {
                if (descEl.scrollHeight > descEl.clientHeight) {
                    moreBtn.classList.remove('hidden');
                }
            }, 100);

            // Social Media Direct Link Button
            socialBtn.href = video.social_media_url;
            
            const platformIcon = getPlatformIcon(video.social_media_platform);
            socialIcon.className = `fa-brands ${platformIcon}`;
            
            // Set platform-specific inline styles for background and border
            let bgStyle = '';
            let borderStyle = 'none';

            switch(video.social_media_platform.toLowerCase()) {
                case 'instagram':
                    bgStyle = 'linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)';
                    break;
                case 'tiktok':
                    bgStyle = '#000000';
                    borderStyle = '1px solid #333333';
                    break;
                case 'youtube':
                    bgStyle = '#ff0000';
                    break;
                case 'whatsapp':
                    bgStyle = '#25d366';
                    break;
                case 'shopee':
                    bgStyle = '#ee4d2d';
                    break;
                default:
                    bgStyle = '#d97706';
            }
            socialBtn.style.background = bgStyle;
            socialBtn.style.border = borderStyle;
            socialBtn.style.color = '#ffffff';

            // Apply global volume state
            player.muted = isMuted;

            // Auto play
            player.play().catch(err => {
                console.log("Autoplay was prevented by browser security.", err);
            });
        }

        function toggleFullDescription(event) {
            if (event) event.stopPropagation();
            const descEl = document.getElementById('shorts-desc');
            const moreBtn = document.getElementById('shorts-more-btn');
            const descContainer = document.getElementById('shorts-desc-container');
            
            if (descEl.classList.contains('line-clamp-3')) {
                // Expand
                descEl.classList.remove('line-clamp-3');
                descContainer.classList.remove('overflow-hidden', 'max-h-[4.5em]');
                descContainer.classList.add('overflow-y-auto', 'max-h-[25vh]', 'bg-black/40', 'backdrop-blur-md', 'p-3', 'rounded-xl', 'border', 'border-white/10', 'mt-1');
                moreBtn.textContent = 'sembunyikan';
            } else {
                // Collapse
                descContainer.classList.remove('overflow-y-auto', 'max-h-[25vh]', 'bg-black/40', 'backdrop-blur-md', 'p-3', 'rounded-xl', 'border', 'border-white/10', 'mt-1');
                descContainer.classList.add('overflow-hidden', 'max-h-[4.5em]');
                moreBtn.textContent = 'selengkapnya';
                descContainer.scrollTop = 0;
                
                // Delay setting line-clamp-3 until the height collapse transition completes
                setTimeout(() => {
                    if (moreBtn.textContent === 'selengkapnya') {
                        descEl.classList.add('line-clamp-3');
                    }
                }, 300);
            }
        }

        function togglePlayPause() {
            if (player.paused) {
                player.play();
                flashIndicator('fa-play');
            } else {
                player.pause();
                flashIndicator('fa-pause');
            }
            
            // Auto unmute on first click interaction
            if (player.muted) {
                player.muted = false;
                isMuted = false;
            }
        }

        function flashIndicator(iconClass) {
            indicatorIcon.className = `fa-solid ${iconClass}`;
            indicator.classList.remove('opacity-0');
            indicator.classList.add('opacity-100');
            setTimeout(() => {
                indicator.classList.remove('opacity-100');
                indicator.classList.add('opacity-0');
            }, 500);
        }

        function nextShort() {
            if (currentVideoIndex < currentVideos.length - 1) {
                currentVideoIndex++;
                loadVideo(currentVideoIndex);
            } else {
                // Loop back to start
                currentVideoIndex = 0;
                loadVideo(currentVideoIndex);
            }
        }

        function prevShort() {
            if (currentVideoIndex > 0) {
                currentVideoIndex--;
                loadVideo(currentVideoIndex);
            } else {
                // Loop to end
                currentVideoIndex = currentVideos.length - 1;
                loadVideo(currentVideoIndex);
            }
        }

        // Key bindings for desktop navigation
        document.addEventListener('keydown', function(event) {
            if (!modal.classList.contains('hidden')) {
                if (event.key === 'Escape') {
                    closeShortsModal();
                } else if (event.key === 'ArrowUp') {
                    prevShort();
                } else if (event.key === 'ArrowDown') {
                    nextShort();
                } else if (event.key === 'ArrowRight') {
                    nextShort();
                } else if (event.key === 'ArrowLeft') {
                    prevShort();
                } else if (event.key === ' ') {
                    event.preventDefault();
                    togglePlayPause();
                }
            }
        });

        // Swipe Gestures for Mobile
        let touchStartY = 0;
        let touchEndY = 0;

        const shortsContainer = document.getElementById('shorts-container');

        shortsContainer.addEventListener('touchstart', function(event) {
            touchStartY = event.changedTouches[0].screenY;
        }, false);

        shortsContainer.addEventListener('touchend', function(event) {
            touchEndY = event.changedTouches[0].screenY;
            handleSwipeGesture();
        }, false);

        // Prevent swipe gestures when interacting/scrolling within description
        const descContainer = document.getElementById('shorts-desc-container');
        descContainer.addEventListener('touchstart', function(event) {
            event.stopPropagation();
        }, { passive: true });
        descContainer.addEventListener('touchend', function(event) {
            event.stopPropagation();
        }, { passive: true });

        function handleSwipeGesture() {
            const distance = touchStartY - touchEndY;
            // Swipe Up -> Next Video
            if (distance > 50) {
                nextShort();
            } 
            // Swipe Down -> Prev Video
            else if (distance < -50) {
                prevShort();
            }
        }

        function getPlatformIcon(platform) {
            switch(platform) {
                case 'instagram': return 'fa-instagram';
                case 'tiktok': return 'fa-tiktok';
                case 'youtube': return 'fa-youtube';
                case 'whatsapp': return 'fa-whatsapp';
                case 'shopee': return 'fa-bag-shopping';
                default: return 'fa-link';
            }
        }

        function getPlatformColorClass(platform) {
            switch(platform) {
                case 'instagram': return 'bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600 text-white';
                case 'tiktok': return 'bg-black text-white border border-gray-700';
                case 'youtube': return 'bg-red-600 text-white';
                case 'whatsapp': return 'bg-emerald-500 text-white';
                case 'shopee': return 'bg-orange-500 text-white';
                default: return 'bg-amber-600 text-white';
            }
        }
    </script>
    @endpush
@endif
