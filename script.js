// Clock Functionality
function updateClock() {
    const clockEl = document.getElementById('clock');
    if (!clockEl) return;

    const now = new Date();
    clockEl.textContent = now.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
}

// Update "Last Updated" relative time
let secsSinceUpdate = 0;
function updateTimeCounter() {
    const updateEl = document.getElementById('lastUpdate');
    const countEl = document.getElementById('articleCount');
    if (!updateEl) return;

    secsSinceUpdate++;
    if (secsSinceUpdate < 60) {
        updateEl.textContent = secsSinceUpdate < 2 ? "à l'instant" : `il y a ${secsSinceUpdate}s`;
    } else {
        const mins = Math.floor(secsSinceUpdate / 60);
        updateEl.textContent = `il y a ${mins} min`;
    }

    // Simulate new articles arriving
    if (secsSinceUpdate % 30 === 0) {
        let count = parseInt(countEl.textContent);
        countEl.textContent = count + 1;
        secsSinceUpdate = 0; // Reset counter for "just now"
    }
}

// Dark Mode Toggle
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    // Update icon if needed
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const sunIcon = document.querySelector('.theme-switch .sun');
    // You could swap icons here or rotate
    if (theme === 'dark') {
        sunIcon.style.transform = 'rotate(180deg)';
    } else {
        sunIcon.style.transform = 'rotate(0deg)';
    }
}

// Search Overlay (Simulated)
const searchTrigger = document.querySelector('.search-trigger');
if (searchTrigger) {
    searchTrigger.addEventListener('click', () => {
        const query = prompt("Rechercher un article...");
        if (query) {
            console.log("Searching for:", query);
            // In a real app, this would redirect to search.php?q=...
        }
    });
}

// Newsletter Form Handling
const newsForm = document.querySelector('.news-form');
if (newsForm) {
    newsForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = newsForm.querySelector('input').value;
        alert(`Merci ! L'adresse ${email} a été ajoutée à notre newsletter.`);
        newsForm.reset();
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Check saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // Start timers
    setInterval(updateClock, 1000);
    setInterval(updateTimeCounter, 1000);
    updateClock();
});

// Smooth Scroll for Navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 80, // Offset for sticky nav
                behavior: 'smooth'
            });
        }
    });
});
