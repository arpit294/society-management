/**
 * ============================================================================
 * 3D & 4D INTERACTIVE ANIMATIONS & ENGINE (Society Management System)
 * UNIVERSAL & 100% CLICK-SAFE FOR ALL BUTTONS, TABLES, AND FORMS ACROSS ALL PAGES
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log("🌌 Initializing SMP 3D & 4D Animation Engine (Universal Click-Safe Mode)...");

    /* ========================================================================
     * 1. SAFE 3D VANILLA-TILT INITIALIZATION (ONLY ON VISUAL STAT/KPI CARDS)
     * ======================================================================== */
    const init3DTilt = () => {
        if (typeof VanillaTilt !== 'undefined') {
            // Target visual KPI and stat cards across all pages
            const tiltTargets = document.querySelectorAll('.dash-card, .kpi-card, .stat-box, .interactive-3d, .auth-card .card, .widget-card');
            
            // CRITICAL SAFETY FILTER: NEVER tilt cards that contain data tables, forms, or interactive DataTables!
            // Tilting tables or forms while typing/clicking breaks mouse click coordinates and ruins UX.
            const safeTiltTargets = Array.from(tiltTargets).filter(card => {
                return !card.querySelector('table, form, .dataTables_wrapper, input, select, textarea');
            });

            if (safeTiltTargets.length > 0) {
                VanillaTilt.init(safeTiltTargets, {
                    max: 6,                   // Gentle, premium tilt rotation (degrees)
                    perspective: 1000,        // Transform perspective
                    scale: 1.01,              // Subtle zoom scale on hover
                    speed: 800,               // Smooth transition speed
                    glare: true,              // Enable subtle metallic glare
                    "max-glare": 0.15,        // Gentle glare opacity
                    "glare-prerender": false, // Automatically create glare element
                    gyroscope: false          // Disable gyroscope to prevent jiggling on laptops/mobile
                });
                console.log(`✨ 3D Tilt initialized safely on ${safeTiltTargets.length} visual widgets.`);
            }
        }
    };

    init3DTilt();

    /* ========================================================================
     * 2. UNIVERSAL 4D CURSOR SPOTLIGHT TRACKING ENGINE
     * ======================================================================== */
    const init4DSpotlight = () => {
        // Target ALL cards across the entire application (Flats, Residents, Complaints, Expenses, Bills, Users, Settings)
        const cards = document.querySelectorAll('.card, .dash-card, .auth-card .card, .spotlight-4d, .kpi-card, .stat-card, .widget-card');
        
        cards.forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });

            card.addEventListener('mouseleave', () => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--mouse-x', `${rect.width / 2}px`);
                card.style.setProperty('--mouse-y', `${rect.height / 2}px`);
            });
        });
        console.log(`💡 4D Cursor Spotlight active on ${cards.length} cards across the app.`);
    };

    init4DSpotlight();

    /* ========================================================================
     * 3. 3D VANTA.JS / THREE.JS INTERACTIVE BACKGROUND (FOR AUTH PAGES)
     * ======================================================================== */
    const init3DBackground = () => {
        // Automatically detect if we are on login, register, forgot/reset password, or auth views
        const isAuthPage = document.querySelector('.auth-page') || 
                           document.querySelector('form[action*="login"]') || 
                           document.querySelector('form[action*="register"]') || 
                           document.querySelector('form[action*="password"]') ||
                           window.location.pathname.includes('login') ||
                           window.location.pathname.includes('register') ||
                           window.location.pathname.includes('password');

        if (isAuthPage && typeof VANTA !== 'undefined' && typeof THREE !== 'undefined') {
            let bgCanvas = document.getElementById('vanta-bg-canvas');
            if (!bgCanvas) {
                bgCanvas = document.createElement('div');
                bgCanvas.id = 'vanta-bg-canvas';
                document.body.prepend(bgCanvas);
            }

            // Ensure background canvas NEVER intercepts mouse clicks
            bgCanvas.style.pointerEvents = 'none';
            bgCanvas.style.zIndex = '-1';

            try {
                VANTA.NET({
                    el: "#vanta-bg-canvas",
                    mouseControls: true,
                    touchControls: true,
                    gyroControls: false,
                    minHeight: 200.00,
                    minWidth: 200.00,
                    scale: 1.00,
                    scaleMobile: 1.00,
                    color: 0x6366f1,
                    backgroundColor: 0x0f172a,
                    points: 14.00,
                    maxDistance: 22.00,
                    spacing: 18.00,
                    showDots: true
                });
                console.log("🌐 3D Vanta.js NET Background Active on Auth Page!");
            } catch (err) {
                console.error("Vanta initialization failed:", err);
            }
        }
    };

    setTimeout(init3DBackground, 200);

    /* ========================================================================
     * 4. 4D INTERACTIVE CLICK RIPPLE WAVE EFFECT (PHYSICS)
     * ======================================================================== */
    document.addEventListener('click', function (e) {
        // Only trigger ripple on visual areas or button clicks, without blocking default click behavior
        const ripple = document.createElement('div');
        ripple.className = 'ripple-wave-4d';
        ripple.style.left = `${e.pageX}px`;
        ripple.style.top = `${e.pageY}px`;
        document.body.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 750);
    }, { passive: true });

    /* ========================================================================
     * 5. 3D FLOATING BADGE INJECTOR ON DASHBOARD / HEADERS
     * ======================================================================== */
    const inject3DBadge = () => {
        const dashTitle = document.querySelector('.card-title');
        if (dashTitle && !document.querySelector('.smp-3d-badge') && window.location.pathname.includes('dashboard')) {
            const badge = document.createElement('span');
            badge.className = 'smp-3d-badge ms-3';
            badge.innerHTML = '<i class="fa-solid fa-cube fa-spin"></i> 3D & 4D Engine Active';
            dashTitle.appendChild(badge);
        }
    };

    inject3DBadge();

    /* ========================================================================
     * 6. UNIVERSAL 3D ORIGAMI FOLD & SCROLL REVEAL ENGINE
     * ======================================================================== */
    const init3DScrollReveal = () => {
        // Target all major visual containers across ALL pages (dashboard, flats, residents, complaints, bills, expenses, etc.)
        const scrollTargets = document.querySelectorAll(
            '.dash-card, .chart-container, .kpi-card, .stat-card, .activity-feed, .card:not(.no-reveal):not(.alert)'
        );

        if (scrollTargets.length === 0) return;

        // Exclude alerts and flash messages from being hidden so errors/toasts are immediately visible
        const validTargets = Array.from(scrollTargets).filter(el => {
            return !el.classList.contains('alert') && !el.closest('.modal');
        });

        validTargets.forEach((el, index) => {
            el.classList.add('scroll-3d-hidden');
            const staggerDelay = (index % 5) * 60;
            el.dataset.staggerDelay = staggerDelay;
            el.style.transitionDelay = `${staggerDelay}ms`;
        });

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -20px 0px',
            threshold: 0.05
        };

        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.transitionDelay = `${entry.target.dataset.staggerDelay || 0}ms`;
                    entry.target.classList.add('scroll-3d-visible');
                    entry.target.classList.remove('scroll-3d-hidden');
                }
            });
        }, observerOptions);

        validTargets.forEach(target => scrollObserver.observe(target));

        // FAILSAFE GUARANTEE: After 800ms, automatically reveal any card near or within viewport even if scrolling hasn't occurred.
        // This guarantees NO card or button EVER gets stuck in an unclickable or invisible state!
        setTimeout(() => {
            validTargets.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight * 1.3 && el.classList.contains('scroll-3d-hidden')) {
                    el.classList.add('scroll-3d-visible');
                    el.classList.remove('scroll-3d-hidden');
                }
            });
        }, 800);

        console.log(`📜 Universal 3D Scroll Reveal observing ${validTargets.length} widgets across app.`);
    };

    setTimeout(init3DScrollReveal, 100);

    /* ========================================================================
     * 7. 4D NEON CYBERPUNK SCROLL PROGRESS BAR
     * ======================================================================== */
    const initScrollProgressBar = () => {
        if (!document.getElementById('smp-scroll-progress-container')) {
            const container = document.createElement('div');
            container.id = 'smp-scroll-progress-container';
            container.innerHTML = '<div id="smp-scroll-progress-bar"></div>';
            document.body.prepend(container);
        }

        const progressBar = document.getElementById('smp-scroll-progress-bar');

        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrollPercent = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
            if (progressBar) {
                progressBar.style.width = `${scrollPercent}%`;
            }
        }, { passive: true });
    };

    initScrollProgressBar();
});
