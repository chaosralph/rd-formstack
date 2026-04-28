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

    if (navToggle && mainNav) {
        const setNavState = (open) => {
            mainNav.classList.toggle('open', open);
            navToggle.setAttribute('aria-expanded', String(open));
            navToggle.setAttribute('aria-label', open ? 'Navigation schließen' : 'Navigation öffnen');
            document.body.classList.toggle('nav-open', open && window.innerWidth <= 760);
        };

        navToggle.addEventListener('click', () => {
            setNavState(!mainNav.classList.contains('open'));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setNavState(false);
            }
        });

        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }
            if (!mainNav.contains(target) && !navToggle.contains(target)) {
                setNavState(false);
            }
        });

        mainNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                setNavState(false);
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 760) {
                setNavState(false);
            }
        });

        setNavState(false);
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
