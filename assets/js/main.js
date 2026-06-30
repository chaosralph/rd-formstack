/**
 * Haupt-JavaScript - RD Formstack Solutions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Menü schließen bei Klick auf Link
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });
    }
    
    // Smooth Scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Cookie Banner
    initCookieBanner();
});

/**
 * Cookie Banner Logik
 */
function initCookieBanner() {
    const banner = document.getElementById('cookieBanner');
    if (!banner) return;

    const consent = localStorage.getItem('cookie_consent');
    if (!consent) {
        // Banner nach kurzer Verzögerung anzeigen
        setTimeout(() => {
            banner.classList.add('show');
        }, 1000);
    }
}

function acceptCookies() {
    localStorage.setItem('cookie_consent', 'accepted');
    document.cookie = 'cookie_consent=accepted; max-age=31536000; path=/; SameSite=Lax';
    hideCookieBanner();
}

function declineCookies() {
    localStorage.setItem('cookie_consent', 'essential');
    document.cookie = 'cookie_consent=essential; max-age=31536000; path=/; SameSite=Lax';
    hideCookieBanner();
}

function hideCookieBanner() {
    const banner = document.getElementById('cookieBanner');
    if (banner) {
        banner.classList.remove('show');
        setTimeout(() => {
            banner.style.display = 'none';
        }, 400);
    }
}
