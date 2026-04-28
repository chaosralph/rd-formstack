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
        navToggle.addEventListener('click', () => {
            const open = mainNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }
            if (!mainNav.contains(target) && !navToggle.contains(target)) {
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });

        mainNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 760) {
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
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

    const form = document.getElementById('contact-form');
    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
})();
