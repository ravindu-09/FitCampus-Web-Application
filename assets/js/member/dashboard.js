// assets/js/member/dashboard.js
document.addEventListener('DOMContentLoaded', () => {
    // Rules Carousel Controls
    const rulesCarousel = document.getElementById('rulesCarousel');
    const btnLeft = document.getElementById('rulesScrollLeft');
    const btnRight = document.getElementById('rulesScrollRight');

    if (rulesCarousel && btnLeft && btnRight) {
        btnLeft.addEventListener('click', () => {
            rulesCarousel.scrollBy({ left: -320, behavior: 'smooth' });
        });
        btnRight.addEventListener('click', () => {
            rulesCarousel.scrollBy({ left: 320, behavior: 'smooth' });
        });
    }
});