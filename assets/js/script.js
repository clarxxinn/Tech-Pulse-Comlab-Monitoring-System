document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (event) {
        const targetId = link.getAttribute('href').slice(1);
        const target = document.getElementById(targetId);

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

const navbar = document.querySelector('.navbar');

if (navbar) {
    window.addEventListener('scroll', function () {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
}

const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');
        }
    });
}, { threshold: 0.15, rootMargin: '0px 0px -100px 0px' });

document.querySelectorAll('.animate-on-scroll').forEach(function (element) {
    observer.observe(element);
});

const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
        const isOpen = navMenu.classList.toggle('active');
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    function closeMenu() {
        navMenu.classList.remove('active');
        navToggle.setAttribute('aria-expanded', 'false');
    }

    navMenu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            closeMenu();
        }
    });
}
