(function () {
    var navToggle = document.getElementById('nav-toggle');
    var mainNav = document.getElementById('main-nav');

    var setMenuState = function (open) {
        if (!navToggle || !mainNav) {
            return;
        }

        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        mainNav.classList.toggle('is-open', open);
        document.body.classList.toggle('nav-open', open);
    };

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var expanded = navToggle.getAttribute('aria-expanded') === 'true';
            setMenuState(!expanded);
        });

        mainNav.addEventListener('click', function (event) {
            var target = event.target;
            if (target instanceof HTMLAnchorElement) {
                setMenuState(false);
            }
        });

        document.addEventListener('click', function (event) {
            var target = event.target;
            var expanded = navToggle.getAttribute('aria-expanded') === 'true';
            if (!expanded) {
                return;
            }

            if (target instanceof Node && !mainNav.contains(target) && !navToggle.contains(target)) {
                setMenuState(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setMenuState(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 880) {
                setMenuState(false);
            }
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLAnchorElement)) {
            return;
        }

        var href = target.getAttribute('href') || '';
        if (href.charAt(0) !== '#') {
            return;
        }

        var destination = document.querySelector(href);
        if (!(destination instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        destination.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    var message = document.getElementById('message');
    var messageCounter = document.getElementById('message-counter');

    if (message && messageCounter) {
        var updateCounter = function () {
            var currentLength = message.value.length;
            var maxLength = Number(message.getAttribute('maxlength') || 0);
            messageCounter.textContent = currentLength + ' / ' + maxLength + ' Zeichen';
        };

        updateCounter();
        message.addEventListener('input', updateCounter);
    }
})();
