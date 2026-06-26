import './bootstrap';
import gsap from 'gsap';
import feather from 'feather-icons';

// Initialize animations when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    feather.replace();
    // Hero Animation
    const heroTitle = document.querySelector('.hero-title');
    const heroSubtitle = document.querySelector('.hero-subtitle');

    // Initial Text Animation
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    if (heroTitle) {
        tl.from(heroTitle, {
            duration: 1,
            y: 50,
            opacity: 0,
            stagger: 0.2
        });
    }

    if (heroSubtitle) {
        tl.from(heroSubtitle, {
            duration: 1,
            y: 30,
            opacity: 0
        }, "-=0.8");
    }

    // Carousel Animation setup function
    const setupCarousel = (selector) => {
        const slides = document.querySelectorAll(selector);
        if (slides.length > 0) {
            let currentSlide = 0;
            const totalSlides = slides.length;
            const slideDuration = 5; // Seconds per slide

            const nextSlide = () => {
                const next = (currentSlide + 1) % totalSlides;

                // Fade out current
                gsap.to(slides[currentSlide], {
                    duration: 1.5,
                    opacity: 0,
                    ease: 'power2.inOut'
                });

                // Fade in next
                gsap.to(slides[next], {
                    duration: 1.5,
                    opacity: 1,
                    ease: 'power2.inOut'
                });

                currentSlide = next;
            };

            // Start the loop
            setInterval(nextSlide, slideDuration * 1000);

            // Initial zoom effect for the first slide
            gsap.fromTo(slides[0],
                { scale: 1.1 },
                { scale: 1, duration: 10, ease: 'none', repeat: -1, yoyo: true } // Subtle continuous zoom
            );
        }
    };

    // Initialize both carousels independently
    setupCarousel('.hero-slide-desktop');
    setupCarousel('.hero-slide-mobile');

    // Navbar - Already has solid background, no scroll effects needed
    // This ensures navbar is always visible and not transparent

    // Slider Logic
    const sliders = document.querySelectorAll('.slider-container');
    sliders.forEach(slider => {
        const container = slider.querySelector('.slider-scroll');
        const prevBtn = slider.querySelector('.prev-btn');
        const nextBtn = slider.querySelector('.next-btn');
        // Optional: Pagination dots if we want to implement them dynamically
        // const dotsContainer = slider.querySelector('.pagination-dots'); 

        if (container && prevBtn && nextBtn) {

            // Scroll Amount (width of one item approx + gap)
            // We can calculate this dynamically or just scroll by container width / 2
            const scrollAmount = 300;

            prevBtn.addEventListener('click', () => {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });

            nextBtn.addEventListener('click', () => {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });

            // Update button states (opacity) based on scroll position
            const updateButtons = () => {
                prevBtn.style.opacity = container.scrollLeft <= 0 ? '0.5' : '1';
                prevBtn.style.pointerEvents = container.scrollLeft <= 0 ? 'none' : 'auto';

                const maxScroll = container.scrollWidth - container.clientWidth;
                nextBtn.style.opacity = container.scrollLeft >= maxScroll - 10 ? '0.5' : '1';
                nextBtn.style.pointerEvents = container.scrollLeft >= maxScroll - 10 ? 'none' : 'auto';
            };

            container.addEventListener('scroll', updateButtons);
            // Initial check
            updateButtons();
        }
    });
});
