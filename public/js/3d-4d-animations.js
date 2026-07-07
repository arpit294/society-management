/**
 * ============================================================================
 * 3D & 4D INTERACTIVE ANIMATIONS & ENGINE (Society Management System)
 * UNIVERSAL, 100% CLICK-SAFE, & CONTINUOUS REPEAT ANIMATIONS (NO REFRESH NEEDED)
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log("🌌 Initializing SMP 3D & 4D Animation Engine (Continuous Repeat & Click-Safe Mode)...");

    /* ========================================================================
     * 1. SAFE 3D VANILLA-TILT INITIALIZATION (ONLY ON VISUAL STAT/KPI CARDS)
     * ======================================================================== */
    const init3DTilt = () => {
        if (typeof VanillaTilt !== 'undefined') {
            const tiltTargets = document.querySelectorAll('.dash-card, .kpi-card, .stat-box, .interactive-3d, .auth-card .card, .widget-card, .kpi-hero-card');
            
            // CRITICAL SAFETY FILTER: NEVER tilt cards that contain data tables, forms, or interactive DataTables!
            const safeTiltTargets = Array.from(tiltTargets).filter(card => {
                return !card.querySelector('table, form, .dataTables_wrapper, input, select, textarea');
            });

            if (safeTiltTargets.length > 0) {
                // Destroy previous tilt instances if re-initializing after AJAX
                safeTiltTargets.forEach(el => {
                    if (el.vanillaTilt) el.vanillaTilt.destroy();
                });

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
            }
        }
    };

    /* ========================================================================
     * 2. UNIVERSAL 4D CURSOR SPOTLIGHT TRACKING ENGINE
     * ======================================================================== */
    const init4DSpotlight = () => {
        const cards = document.querySelectorAll('.kpi-hero-card, .dash-card, .auth-card .card, .spotlight-4d, .kpi-card, .stat-card, .widget-card');
        
        cards.forEach(card => {
            if (card.dataset.spotlightActive) return; // Avoid duplicate listeners on AJAX refresh
            card.dataset.spotlightActive = "true";

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
    };

    /* ========================================================================
     * 3. 3D VANTA.JS / THREE.JS INTERACTIVE BACKGROUND (FOR AUTH PAGES)
     * ======================================================================== */
    const init3DBackground = () => {
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

            bgCanvas.style.pointerEvents = 'none';
            bgCanvas.style.zIndex = '-1';

            try {
                if (!window.vantaEffect) {
                    window.vantaEffect = VANTA.NET({
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
                }
            } catch (err) {
                console.error("Vanta initialization failed:", err);
            }
        }
    };

    /* ========================================================================
     * 4. 4D INTERACTIVE CLICK RIPPLE WAVE EFFECT (PHYSICS)
     * ======================================================================== */
    if (!window.smpRippleInitialized) {
        window.smpRippleInitialized = true;
        document.addEventListener('click', function (e) {
            const ripple = document.createElement('div');
            ripple.className = 'ripple-wave-4d';
            ripple.style.left = `${e.pageX}px`;
            ripple.style.top = `${e.pageY}px`;
            document.body.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 750);
        }, { passive: true });
    }

    /* ========================================================================
     * 5. 3D FLOATING BADGE INJECTOR ON DASHBOARD
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

    /* ========================================================================
     * 6. CONTINUOUS REPEAT ORIGAMI SCROLL REVEAL ENGINE (EVERY TIME!)
     * ======================================================================== */
    let scrollObserver = null;
    const init3DScrollReveal = () => {
        const scrollTargets = document.querySelectorAll(
            '.dash-card, .chart-container, .kpi-card, .stat-card, .activity-feed, .card:not(.no-reveal):not(.alert), .activity-row-item, .kpi-hero-card, .widget-card'
        );

        if (scrollTargets.length === 0) return;

        const validTargets = Array.from(scrollTargets).filter(el => {
            return !el.classList.contains('alert') && !el.closest('.modal');
        });

        if (scrollObserver) scrollObserver.disconnect();

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -30px 0px',
            threshold: 0.05
        };

        scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Animate IN when scrolling into viewport
                    entry.target.style.transitionDelay = `${entry.target.dataset.staggerDelay || 0}ms`;
                    entry.target.classList.add('scroll-3d-visible');
                    entry.target.classList.remove('scroll-3d-hidden');

                    // CRITICAL: Re-trigger Chart.js animation EVERY TIME chart scrolls into view!
                    const canvases = entry.target.querySelectorAll('canvas');
                    if (entry.target.tagName === 'CANVAS') canvases.push(entry.target);
                    canvases.forEach(canvas => {
                        if (typeof Chart !== 'undefined' && Chart.getChart) {
                            const chart = Chart.getChart(canvas);
                            if (chart && !canvas.dataset.animating) {
                                canvas.dataset.animating = "true";
                                setTimeout(() => {
                                    chart.reset();
                                    chart.update();
                                    setTimeout(() => { canvas.dataset.animating = ""; }, 800);
                                }, Number(entry.target.dataset.staggerDelay || 0));
                            }
                        }
                    });
                } else {
                    // CRITICAL: Reset when scrolled out of viewport so animation plays EVERY TIME without page refresh!
                    entry.target.style.transitionDelay = '0ms';
                    entry.target.classList.remove('scroll-3d-visible');
                    entry.target.classList.add('scroll-3d-hidden');

                    // Also reset chart to starting state when scrolled out of view so it re-animates next time!
                    const canvases = entry.target.querySelectorAll('canvas');
                    if (entry.target.tagName === 'CANVAS') canvases.push(entry.target);
                    canvases.forEach(canvas => {
                        if (typeof Chart !== 'undefined' && Chart.getChart) {
                            const chart = Chart.getChart(canvas);
                            if (chart) {
                                chart.reset();
                            }
                        }
                    });
                }
            });
        }, observerOptions);

        validTargets.forEach((el, index) => {
            if (!el.classList.contains('scroll-3d-visible')) {
                el.classList.add('scroll-3d-hidden');
            }
            const staggerDelay = (index % 5) * 60;
            el.dataset.staggerDelay = staggerDelay;
            scrollObserver.observe(el);
        });

        // FAILSAFE: Guarantee that ONLY elements currently visible in the INITIAL top viewport are shown!
        // Elements below the fold stay hidden until scrolled into view so they animate EVERY TIME!
        setTimeout(() => {
            validTargets.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top >= 0 && rect.top < window.innerHeight * 0.85 && rect.bottom > 0 && el.classList.contains('scroll-3d-hidden')) {
                    el.classList.add('scroll-3d-visible');
                    el.classList.remove('scroll-3d-hidden');
                }
            });
        }, 300);
    };

    /* ========================================================================
     * 7. CONTINUOUS REPEAT COUNTER ANIMATION (EVERY TIME STATS ENTER VIEWPORT)
     * ======================================================================== */
    let counterObserver = null;
    const initCounterAnimation = () => {
        const counters = document.querySelectorAll(".counter-animate");
        if (counters.length === 0) return;

        if (counterObserver) counterObserver.disconnect();

        counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const counter = entry.target;
                const targetVal = +counter.getAttribute("data-target") || 0;
                const speed = 50; // Smooth speed

                if (entry.isIntersecting) {
                    // Animate count up every time the card scrolls into view!
                    let currentVal = 0;
                    const inc = Math.max(1, Math.ceil(targetVal / speed));
                    
                    if (counter.animationTimer) clearInterval(counter.animationTimer);

                    counter.animationTimer = setInterval(() => {
                        currentVal += inc;
                        if (currentVal >= targetVal) {
                            counter.innerText = targetVal.toLocaleString();
                            clearInterval(counter.animationTimer);
                        } else {
                            counter.innerText = currentVal.toLocaleString();
                        }
                    }, 20);
                } else {
                    // Reset to 0 when scrolled out of viewport so it counts up again next time!
                    if (counter.animationTimer) clearInterval(counter.animationTimer);
                    counter.innerText = "0";
                }
            });
        }, { threshold: 0.05 });

        counters.forEach(counter => counterObserver.observe(counter));
    };

    /* ========================================================================
     * 8. 4D NEON CYBERPUNK SCROLL PROGRESS BAR
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

    /* ========================================================================
     * 9. GLOBAL REFRESH & AJAX MUTATION OBSERVER (WORKS WITHOUT PAGE REFRESH!)
     * ======================================================================== */
    window.refreshSMPAnimations = () => {
        init3DTilt();
        init4DSpotlight();
        init3DBackground();
        inject3DBadge();
        init3DScrollReveal();
        initCounterAnimation();
        initScrollProgressBar();
    };

    // Run initial setup
    window.refreshSMPAnimations();

    // Attach MutationObserver to detect AJAX content loads, modal opens, or tab switches automatically
    if (!window.smpMutationObserver) {
        let mutationTimeout = null;
        window.smpMutationObserver = new MutationObserver((mutations) => {
            let shouldRefresh = false;
            for (const mutation of mutations) {
                if (mutation.addedNodes.length > 0) {
                    for (const node of mutation.addedNodes) {
                        if (node.nodeType === 1 && (node.classList.contains('card') || node.querySelector('.card, .counter-animate'))) {
                            shouldRefresh = true;
                            break;
                        }
                    }
                }
                if (shouldRefresh) break;
            }

            if (shouldRefresh) {
                clearTimeout(mutationTimeout);
                mutationTimeout = setTimeout(() => {
                    console.log("🔄 AJAX/DOM change detected! Re-triggering SMP Animations without refresh...");
                    window.refreshSMPAnimations();
                }, 150);
            }
        });

        window.smpMutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});
