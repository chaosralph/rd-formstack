(function () {
    var progressBar = document.getElementById('scroll-progress-bar');
    var navToggle = document.getElementById('nav-toggle');
    var mainNav = document.getElementById('main-nav');
    var main = document.getElementById('main');

    if (main) {
        main.setAttribute('tabindex', '-1');
    }

    if (progressBar) {
        var updateScrollProgress = function () {
            var scrollTop = window.scrollY;
            var scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
            var percent = scrollHeight > 0 ? Math.min((scrollTop / scrollHeight) * 100, 100) : 0;
            progressBar.style.width = percent + '%';
        };

        updateScrollProgress();
        window.addEventListener('scroll', updateScrollProgress, { passive: true });
        window.addEventListener('resize', updateScrollProgress);
    }

    var getFocusableNavItems = function () {
        if (!mainNav) {
            return [];
        }

        var selectors = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
        return Array.prototype.slice.call(mainNav.querySelectorAll(selectors));
    };

    var setMenuState = function (open) {
        if (!navToggle || !mainNav) {
            return;
        }

        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        navToggle.setAttribute('aria-label', open ? 'Navigation schließen' : 'Navigation öffnen');
        mainNav.classList.toggle('is-open', open);
        document.body.classList.toggle('nav-open', open);

        if (open) {
            var items = getFocusableNavItems();
            if (items.length > 0) {
                items[0].focus();
            }
        }
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
                var isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    setMenuState(false);
                    navToggle.focus();
                }
            }

            if (event.key === 'Tab' && navToggle.getAttribute('aria-expanded') === 'true') {
                var items = getFocusableNavItems();
                if (items.length === 0) {
                    return;
                }

                var first = items[0];
                var last = items[items.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                setMenuState(false);
            }
        });
    }

    var scrollToAnchor = function (destination, setFocus) {
        var header = document.querySelector('.site-header');
        var headerOffset = header instanceof HTMLElement ? header.offsetHeight + 12 : 0;
        var top = destination.getBoundingClientRect().top + window.pageYOffset - headerOffset;

        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });

        if (setFocus) {
            destination.setAttribute('tabindex', '-1');
            destination.focus({ preventScroll: true });
        }
    };

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLAnchorElement)) {
            return;
        }

        var href = target.getAttribute('href') || '';

        if (href === '#main' && main) {
            event.preventDefault();
            scrollToAnchor(main, true);
            return;
        }

        if (href.charAt(0) !== '#') {
            return;
        }

        var destination = document.querySelector(href);
        if (!(destination instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        scrollToAnchor(destination, false);
    });

    var sectionNavLinks = Array.prototype.slice.call(document.querySelectorAll('.main-nav .nav-link[href^="#"]'));
    var sectionTargets = sectionNavLinks
        .map(function (link) {
            var selector = link.getAttribute('href') || '';
            if (!selector || selector === '#') {
                return null;
            }
            var node = document.querySelector(selector);
            return node instanceof HTMLElement ? { link: link, section: node } : null;
        })
        .filter(function (entry) { return entry !== null; });

    if (sectionTargets.length > 0) {
        var updateSectionNavState = function () {
            var currentY = window.scrollY + 140;
            var activeIndex = 0;

            sectionTargets.forEach(function (entry, index) {
                if (entry.section.offsetTop <= currentY) {
                    activeIndex = index;
                }
            });

            sectionTargets.forEach(function (entry, index) {
                entry.link.classList.toggle('is-active', index === activeIndex);
            });
        };

        updateSectionNavState();
        window.addEventListener('scroll', updateSectionNavState, { passive: true });
    }

    if ('IntersectionObserver' in window) {
        var revealTargets = Array.prototype.slice.call(document.querySelectorAll('.service-card, .ref-card, .proof-card, .process-card, .quick-link, .contact-sidecard, .form-card'));
        if (revealTargets.length > 0) {
            var revealObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });

            revealTargets.forEach(function (element, index) {
                element.classList.add('reveal');
                element.style.setProperty('--reveal-delay', Math.min(index * 24, 200) + 'ms');
                revealObserver.observe(element);
            });
        }
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

    var form = document.getElementById('contact-form');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    var requiredFields = Array.prototype.slice.call(form.querySelectorAll('[required]'));
    var getFieldLabel = function (field) {
        var fieldId = field.getAttribute('id') || '';
        var label = form.querySelector('label[for="' + fieldId + '"]');
        if (label && label.textContent) {
            return label.textContent.replace('*', '').trim();
        }

        return 'Dieses Feld';
    };

    var clearFieldError = function (field) {
        var errorId = field.getAttribute('id') + '-error';
        var oldError = document.getElementById(errorId);
        if (oldError) {
            oldError.remove();
        }

        var describedBy = (field.getAttribute('aria-describedby') || '').split(' ').filter(Boolean);
        var filtered = describedBy.filter(function (item) { return item !== errorId; });

        if (filtered.length > 0) {
            field.setAttribute('aria-describedby', filtered.join(' '));
        } else {
            field.removeAttribute('aria-describedby');
        }

        field.removeAttribute('aria-invalid');
        field.classList.remove('field-invalid');
    };

    var setFieldError = function (field, messageText) {
        var fieldId = field.getAttribute('id') || '';
        if (fieldId === '') {
            return;
        }

        clearFieldError(field);

        var errorId = fieldId + '-error';
        var error = document.createElement('p');
        error.id = errorId;
        error.className = 'field-error';
        error.textContent = messageText;

        field.insertAdjacentElement('afterend', error);
        field.setAttribute('aria-invalid', 'true');
        field.classList.add('field-invalid');

        var describedBy = (field.getAttribute('aria-describedby') || '').split(' ').filter(Boolean);
        if (describedBy.indexOf(errorId) === -1) {
            describedBy.push(errorId);
        }
        field.setAttribute('aria-describedby', describedBy.join(' '));
    };

    var getFieldError = function (field) {
        if (field.validity.valueMissing) {
            return getFieldLabel(field) + ' ist erforderlich.';
        }

        if (field.validity.typeMismatch && field.getAttribute('type') === 'email') {
            return 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        }

        if (field.validity.tooLong) {
            return getFieldLabel(field) + ' ist zu lang.';
        }

        return '';
    };

    var summary = document.createElement('section');
    summary.className = 'form-error-summary';
    summary.id = 'form-error-summary';
    summary.setAttribute('role', 'alert');
    summary.setAttribute('aria-live', 'assertive');
    summary.setAttribute('aria-atomic', 'true');
    summary.setAttribute('tabindex', '-1');
    summary.hidden = true;
    summary.innerHTML = '<h3>Bitte korrigieren Sie die markierten Felder.</h3><ul></ul>';
    form.insertAdjacentElement('afterbegin', summary);

    var validateField = function (field) {
        var messageText = getFieldError(field);

        if (messageText === '') {
            clearFieldError(field);
            return '';
        }

        setFieldError(field, messageText);
        return messageText;
    };

    requiredFields.forEach(function (field) {
        field.addEventListener('blur', function () {
            validateField(field);
        });

        field.addEventListener('input', function () {
            if (field.getAttribute('aria-invalid') === 'true') {
                validateField(field);
            }
        });
    });

    form.addEventListener('submit', function (event) {
        var errors = [];

        requiredFields.forEach(function (field) {
            var errorText = validateField(field);
            if (errorText !== '') {
                errors.push({ field: field, text: errorText });
            }
        });

        if (errors.length === 0) {
            summary.hidden = true;
            return;
        }

        event.preventDefault();

        var list = summary.querySelector('ul');
        if (list) {
            list.innerHTML = '';

            errors.forEach(function (entry) {
                var item = document.createElement('li');
                var link = document.createElement('a');
                var fieldId = entry.field.getAttribute('id') || '';

                link.href = '#' + fieldId;
                link.textContent = entry.text;
                link.addEventListener('click', function (clickEvent) {
                    clickEvent.preventDefault();
                    entry.field.focus();
                });

                item.appendChild(link);
                list.appendChild(item);
            });
        }

        summary.hidden = false;
        summary.focus();
    });
})();
