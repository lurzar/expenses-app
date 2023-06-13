// Handle light mode or dark mode theme
var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-btn')
var themeToggleLightIcon = document.getElementById('theme-toggle-light-btn')

// Change the icons inside the button based on previous settings
if (localStorage.getItem('theme') === 'night' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: night)').matches)) {
    themeToggleLightIcon.classList.remove('hidden')
} else {
    themeToggleDarkIcon.classList.remove('hidden')
}

themeToggleDarkIcon.addEventListener('click', function() {
    localStorage.theme = 'night'
    themeToggleDarkIcon.classList.toggle('hidden')
    themeToggleLightIcon.classList.remove('hidden')
});

themeToggleLightIcon.addEventListener('click', function() {
    localStorage.theme = 'cupcake'
    themeToggleLightIcon.classList.toggle('hidden')
    themeToggleDarkIcon.classList.remove('hidden')
});