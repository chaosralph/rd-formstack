(() => {
    const header = document.querySelector('.site-header');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('scrolled', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    const navToggle = document.getElementById('nav-toggle');
    const mainNav = document.getElementById('main-nav');
    const mobileNavBreakpoint = 960;

    if (navToggle && mainNav) {

        const getNavFocusableElements = () => {
            const selectors = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
            return Array.from(mainNav.querySelectorAll(selectors)).filter((element) => {
                if (!(element instanceof HTMLElement)) {
                    return false;
                }

                return element.offsetParent !== null;
            });
        };

        const setNavState = (open, options = {}) => {
            const { returnFocus = false } = options;

            mainNav.classList.toggle('open', open);
            navToggle.setAttribute('aria-expanded', String(open));
            navToggle.setAttribute('aria-label', open ? 'Navigation schließen' : 'Navigation öffnen');
            document.body.classList.toggle('nav-open', open && window.innerWidth <= mobileNavBreakpoint);

            if (open && window.innerWidth <= mobileNavBreakpoint) {
                const [firstFocusable] = getNavFocusableElements();
                if (firstFocusable instanceof HTMLElement) {
                    firstFocusable.focus();
                }
            }

            if (!open && returnFocus) {
                navToggle.focus();
            }
        };

        navToggle.addEventListener('click', () => {
            setNavState(!mainNav.classList.contains('open'), { returnFocus: false });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                if (mainNav.classList.contains('open')) {
                    setNavState(false, { returnFocus: true });
                }
                return;
            }

            if (event.key !== 'Tab' || !mainNav.classList.contains('open') || window.innerWidth > mobileNavBreakpoint) {
                return;
            }

            const focusableElements = [navToggle, ...getNavFocusableElements()].filter((element) => element instanceof HTMLElement);
            if (focusableElements.length === 0) {
                return;
            }

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            const activeElement = document.activeElement;

            if (event.shiftKey && activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
                return;
            }

            if (!event.shiftKey && activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        });

        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }
            if (!mainNav.contains(target) && !navToggle.contains(target)) {
                setNavState(false, { returnFocus: false });
            }
        });

        mainNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                setNavState(false, { returnFocus: false });
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > mobileNavBreakpoint) {
                setNavState(false, { returnFocus: false });
            }
        });

        setNavState(false, { returnFocus: false });
    }

    const navLinks = Array.from(document.querySelectorAll('.nav-link[href^="#"]'));
    const sections = navLinks
        .map((link) => document.querySelector(link.getAttribute('href')))
        .filter((section) => section instanceof HTMLElement);

    if (navLinks.length > 0 && sections.length > 0) {
        const activateLink = () => {
            const y = window.scrollY + 130;
            let activeIndex = 0;

            sections.forEach((section, index) => {
                if (section.offsetTop <= y) {
                    activeIndex = index;
                }
            });

            navLinks.forEach((link, index) => {
                link.classList.toggle('is-active', index === activeIndex);
            });
        };

        activateLink();
        window.addEventListener('scroll', activateLink, { passive: true });
    }

    const revealTargets = document.querySelectorAll('.card, .step-card, .trust-item, .callout-card, .placeholder-card');
    if (revealTargets.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealTargets.forEach((target, index) => {
            target.style.setProperty('--reveal-delay', `${Math.min(index * 22, 180)}ms`);
            target.classList.add('reveal');
            observer.observe(target);
        });
    }

    const form = document.getElementById('contact-form');
    if (!form) {
        return;
    }

    const messageInput = document.getElementById('message');
    const messageCounter = document.getElementById('message-counter');
    if (messageInput instanceof HTMLTextAreaElement && messageCounter) {
        const maxLength = Number(messageInput.getAttribute('maxlength') || '6000');
        const updateCounter = () => {
            messageCounter.textContent = `${messageInput.value.length} / ${maxLength} Zeichen`;
        };

        updateCounter();
        messageInput.addEventListener('input', updateCounter);
    }

    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
})();
