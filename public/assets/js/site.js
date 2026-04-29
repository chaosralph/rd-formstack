(function () {
    var navToggle = document.getElementById('nav-toggle');
    var mainNav = document.getElementById('main-nav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            mainNav.classList.toggle('is-open', !expanded);
        });
    }

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
