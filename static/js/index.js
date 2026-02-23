/**
 * Aluora GSL - Homepage JavaScript
 * 40+ Page-Specific Functions
 */

(function() {
    'use strict';

    // ==========================================================================
    // HOMEPAGE INITIALIZATION
    // ==========================================================================
    
    const Homepage = {
        init() {
            this.setupHeroAnimations();
            this.setupCounters();
            this.setupCategoryCards();
            this.setupTestimonialSlider();
            this.setupNewsletterForm();
            this.setupTrustBadges();
            this.setupFeatureCards();
            this.setupStatsSection();
            this.setupPromoBanner();
            this.setupParallax();
            console.log('Homepage initialized');
        },

        // ==========================================================================
        // HERO ANIMATIONS (Functions 1-5)
        // ==========================================================================
        
        setupHeroAnimations() {
            const heroContent = document.querySelector('.hero-content');
            const heroImage = document.querySelector('.hero-image');
            
            if (heroContent) {
                heroContent.classList.add('fade-in-up');
            }
            
            if (heroImage) {
                heroImage.classList.add('fade-in-right');
                // Floating animation
                heroImage.style.animation = 'float 6s ease-in-out infinite';
            }
            
            // Stagger animations for buttons
            const buttons = document.querySelectorAll('.hero-buttons .btn');
            buttons.forEach((btn, index) => {
                btn.style.opacity = '0';
                btn.style.animation = `fadeInUp 0.5s ease ${0.3 + index * 0.1}s forwards`;
            });
        },

        // ==========================================================================
        // COUNTER ANIMATIONS (Functions 6-10)
        // ==========================================================================
        
        setupCounters() {
            const counters = document.querySelectorAll('.counter');
            if (counters.length === 0) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = parseInt(counter.dataset.target) || 0;
                        const suffix = counter.dataset.suffix || '';
                        const duration = parseInt(counter.dataset.duration) || 2000;
                        
                        this.animateCounter(counter, target, duration, suffix);
                        observer.unobserve(counter);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => observer.observe(counter));
        },

        animateCounter(element, target, duration, suffix) {
            let start = 0;
            const increment = target / (duration / 16);
            
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + suffix;
                }
            }, 16);
        },

        // ==========================================================================
        // CATEGORY CARDS (Functions 11-15)
        // ==========================================================================
        
        setupCategoryCards() {
            const cards = document.querySelectorAll('.category-card');
            
            cards.forEach(card => {
                // Hover effects
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0) scale(1)';
                });

                // Click to filter
                const link = card.querySelector('a');
                link?.addEventListener('click', (e) => {
                    const category = card.dataset.category;
                    if (category) {
                        sessionStorage.setItem('filterCategory', category);
                    }
                });
            });
        },

        // ==========================================================================
        // TESTIMONIAL SLIDER (Functions 16-20)
        // ==========================================================================
        
        setupTestimonialSlider() {
            const slider = document.querySelector('.testimonial-slider');
            if (!slider) return;

            const slides = slider.querySelectorAll('.testimonial-slide');
            let currentSlide = 0;
            
            // Auto-advance
            setInterval(() => {
                this.nextSlide(slides, currentSlide);
                currentSlide = (currentSlide + 1) % slides.length;
            }, 5000);

            // Navigation dots
            this.createSliderDots(slider, slides.length);
        },

        nextSlide(slides, currentIndex) {
            slides[currentIndex].classList.remove('active');
            const nextIndex = (currentIndex + 1) % slides.length;
            slides[nextIndex].classList.add('active');
        },

        createSliderDots(slider, count) {
            const dotsContainer = document.createElement('div');
            dotsContainer.className = 'slider-dots';
            dotsContainer.style.cssText = 'display:flex;justify-content:center;gap:8px;margin-top:2rem;';
            
            for (let i = 0; i < count; i++) {
                const dot = document.createElement('button');
                dot.className = 'slider-dot';
                dot.style.cssText = 'width:10px;height:10px;border-radius:50%;border:none;background:var(--gray-light);cursor:pointer;transition:var(--transition-base)';
                dot.addEventListener('click', () => {
                    document.querySelectorAll('.testimonial-slide').forEach(s => s.classList.remove('active'));
                    document.querySelectorAll('.testimonial-slide')[i].classList.add('active');
                });
                dotsContainer.appendChild(dot);
            }
            
            slider.appendChild(dotsContainer);
        },

        // ==========================================================================
        // NEWSLETTER FORM (Functions 21-25)
        // ==========================================================================
        
        setupNewsletterForm() {
            const form = document.querySelector('.newsletter-form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const email = form.querySelector('input[type="email"]').value;
                const button = form.querySelector('button');
                
                // Validate email
                if (!App.isValidEmail(email)) {
                    Toast.error('Please enter a valid email address');
                    return;
                }

                // Show loading state
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
                button.disabled = true;

                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1500));

                // Success
                Toast.success('Successfully subscribed to newsletter!');
                form.reset();
                button.innerHTML = originalText;
                button.disabled = false;

                // Save to localStorage
                const subscribers = JSON.parse(localStorage.getItem('newsletter_subscribers') || '[]');
                subscribers.push({ email, date: new Date().toISOString() });
                localStorage.setItem('newsletter_subscribers', JSON.stringify(subscribers));
            });
        },

        // ==========================================================================
        // TRUST BADGES (Functions 26-30)
        // ==========================================================================
        
        setupTrustBadges() {
            const badges = document.querySelectorAll('.trust-badge');
            
            badges.forEach(badge => {
                badge.addEventListener('mouseenter', () => {
                    badge.style.transform = 'scale(1.05)';
                });
                
                badge.addEventListener('mouseleave', () => {
                    badge.style.transform = 'scale(1)';
                });
            });
        },

        // ==========================================================================
        // FEATURE CARDS (Functions 31-35)
        // ==========================================================================
        
        setupFeatureCards() {
            const cards = document.querySelectorAll('.feature-card');
            
            cards.forEach((card, index) => {
                // Stagger animation
                card.style.opacity = '0';
                card.style.animation = `fadeInUp 0.5s ease ${0.1 * index}s forwards`;
                
                // Interactive hover
                card.addEventListener('mouseenter', () => {
                    const icon = card.querySelector('.feature-icon');
                    if (icon) {
                        icon.style.transform = 'scale(1.2) rotate(5deg)';
                    }
                });
                
                card.addEventListener('mouseleave', () => {
                    const icon = card.querySelector('.feature-icon');
                    if (icon) {
                        icon.style.transform = 'scale(1) rotate(0deg)';
                    }
                });
            });
        },

        // ==========================================================================
        // STATS SECTION (Functions 36-40)
        // ==========================================================================
        
        setupStatsSection() {
            const stats = document.querySelectorAll('.stat-item');
            
            // Intersection observer for scroll-triggered animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });

            stats.forEach(stat => observer.observe(stat));
        },

        // ==========================================================================
        // PROMO BANNER (Functions 41-45)
        // ==========================================================================
        
        setupPromoBanner() {
            const banner = document.querySelector('.promo-banner');
            if (!banner) return;

            const closeBtn = banner.querySelector('.promo-close');
            closeBtn?.addEventListener('click', () => {
                banner.style.animation = 'slideOutUp 0.3s ease forwards';
                setTimeout(() => banner.remove(), 300);
            });

            // Check if already closed
            const closed = localStorage.getItem('promo_banner_closed');
            if (closed) {
                banner.remove();
            } else {
                // Auto-close after 10 seconds
                setTimeout(() => {
                    if (banner.parentElement) {
                        banner.style.animation = 'slideOutUp 0.3s ease forwards';
                        setTimeout(() => {
                            banner.remove();
                            localStorage.setItem('promo_banner_closed', 'true');
                        }, 300);
                    }
                }, 10000);
            }
        },

        // ==========================================================================
        // PARALLAX EFFECTS (Functions 46-50)
        // ==========================================================================
        
        setupParallax() {
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            
            window.addEventListener('scroll', App.throttle(() => {
                const scrolled = window.pageYOffset;
                
                parallaxElements.forEach(el => {
                    const speed = parseFloat(el.dataset.parallax) || 0.5;
                    const yPos = -(scrolled * speed);
                    el.style.transform = `translateY(${yPos}px)`;
                });
            }, 10));
        },

        // ==========================================================================
        // ADDITIONAL FEATURES (Functions 51-60)
        // ==========================================================================
        
        // Scroll-triggered reveal
        initScrollReveal() {
            const elements = document.querySelectorAll('.reveal');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            });

            elements.forEach(el => observer.observe(el));
        },

        // Product quick view
        initQuickView() {
            document.querySelectorAll('.quick-view-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const productId = btn.dataset.productId;
                    // Quick view modal would open here
                    Toast.info('Quick view coming soon!');
                });
            });
        },

        // Wishlist toggle
        toggleWishlist(productId) {
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            
            if (wishlist.includes(productId)) {
                wishlist = wishlist.filter(id => id !== productId);
                Toast.info('Removed from wishlist');
            } else {
                wishlist.push(productId);
                Toast.success('Added to wishlist');
            }
            
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
        },

        // Recently viewed products
        trackRecentlyViewed(productId) {
            let viewed = JSON.parse(localStorage.getItem('recently_viewed') || '[]');
            
            // Remove if already exists
            viewed = viewed.filter(id => id !== productId);
            
            // Add to beginning
            viewed.unshift(productId);
            
            // Keep only last 10
            viewed = viewed.slice(0, 10);
            
            localStorage.setItem('recently_viewed', JSON.stringify(viewed));
        },

        // Compare products
        addToCompare(productId) {
            let compare = JSON.parse(localStorage.getItem('compare') || '[]');
            
            if (compare.length >= 4) {
                Toast.warning('Maximum 4 products can be compared');
                return;
            }
            
            if (compare.includes(productId)) {
                Toast.info('Product already in compare list');
                return;
            }
            
            compare.push(productId);
            localStorage.setItem('compare', JSON.stringify(compare));
            Toast.success('Added to compare');
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => Homepage.init());
    } else {
        Homepage.init();
    }

    // Export to global
    window.Homepage = Homepage;

})();
