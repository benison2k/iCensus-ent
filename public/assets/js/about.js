// public/assets/js/about.js

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.about-card');

    cards.forEach(card => {
        const header = card.querySelector('.about-header');
        header.addEventListener('click', () => {
            card.classList.toggle('active');
            const chevron = header.querySelector('.chevron');
            chevron.classList.toggle('rotated');
        });
    });
    
    // Automatically open the first card
    const firstCard = document.getElementById('card-intro');
    if(firstCard) {
        firstCard.classList.add('active');
        firstCard.querySelector('.chevron').classList.add('rotated');
    }
});