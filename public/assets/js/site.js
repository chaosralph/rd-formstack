(function () {
    var navToggle = document.getElementById('nav-toggle');
    var mainNav = document.getElementById('main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            mainNav.classList.toggle('is-open', !expanded);
        });

        mainNav.addEventListener('click', function (event) {
            var target = event.target;
            if (target instanceof HTMLAnchorElement) {
                navToggle.setAttribute('aria-expanded', 'false');
                mainNav.classList.remove('is-open');
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
