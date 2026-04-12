// Initialize AOS
AOS.init({
    duration: 800,
    once: true,
    offset: 100
});

// Mobile Menu
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileNav = document.getElementById('mobileNav');
const mobileNavClose = document.getElementById('mobileNavClose');

mobileMenuBtn.addEventListener('click', () => {
    mobileNav.classList.add('active');
    document.body.style.overflow = 'hidden';
});

mobileNavClose.addEventListener('click', () => {
    mobileNav.classList.remove('active');
    document.body.style.overflow = 'auto';
});

document.addEventListener('click', (e) => {
    if (mobileNav.classList.contains('active') && !mobileNav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        mobileNav.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Hero Slider
let slideIndex = 1;
const slides = document.querySelectorAll('.hero .slide');
const dots = document.querySelectorAll('.hero-dots .dot');

function showSlide(n) {
    if (n > slides.length) slideIndex = 1;
    if (n < 1) slideIndex = slides.length;
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[slideIndex - 1].classList.add('active');
    dots[slideIndex - 1].classList.add('active');
}

function nextSlide() {
    slideIndex++;
    showSlide(slideIndex);
}

dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        slideIndex = index + 1;
        showSlide(slideIndex);
    });
});

setInterval(nextSlide, 5000);

// Testimonial Slider
let testimonialIndex = 1;
const testimonialSlides = document.querySelectorAll('.testimonial-slide');
const testimonialDots = document.querySelectorAll('.testimonial-dots .dot');

function showTestimonial(n) {
    if (n > testimonialSlides.length) testimonialIndex = 1;
    if (n < 1) testimonialIndex = testimonialSlides.length;
    
    testimonialSlides.forEach(slide => slide.classList.remove('active'));
    testimonialDots.forEach(dot => dot.classList.remove('active'));
    
    testimonialSlides[testimonialIndex - 1].classList.add('active');
    testimonialDots[testimonialIndex - 1].classList.add('active');
}

testimonialDots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        testimonialIndex = index + 1;
        showTestimonial(testimonialIndex);
    });
});

setInterval(() => {
    testimonialIndex++;
    if (testimonialIndex > testimonialSlides.length) testimonialIndex = 1;
    showTestimonial(testimonialIndex);
}, 7000);

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 80,
                behavior: 'smooth'
            });
            // Close mobile menu
            if (mobileNav.classList.contains('active')) {
                mobileNav.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }
    });
});

// Form submission
document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Terima kasih atas masukan Anda! Tim kami akan segera merespon.');
    this.reset();
});

// Active nav link on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        
        if (window.scrollY >= sectionTop - 150) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
});

const $next = document.querySelector('.next');
const $prev = document.querySelector('.prev');

$next.addEventListener(
    'click',
    () => {
        const items =document.querySelectorAll('.item');
        document.querySelector('.slide').appendChild(items[0]);
    },
);

$prev.addEventListener(
    'click',
    () => {
        const items = document.querySelectorAll('.item');
        document.querySelector('.slide').prepend(items[items.length - 1]);
    },
);