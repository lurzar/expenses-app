// Handle light mode or dark mode theme
var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-btn')
var themeToggleLightIcon = document.getElementById('theme-toggle-light-btn')

// Change the icons inside the button based on previous settings
window.onload = function () {
    if (localStorage.getItem('theme') === 'night' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: night)').matches)) {
        themeToggleLightIcon.classList.remove('hidden')
        showLightProp()
    } else {
        themeToggleDarkIcon.classList.remove('hidden')
        showDarkProp()
    }
}

themeToggleDarkIcon.addEventListener('click', function() {
    showLightProp()
    themeToggleDarkIcon.classList.toggle('hidden')
    themeToggleLightIcon.classList.remove('hidden')
});

themeToggleLightIcon.addEventListener('click', function() {
    showDarkProp()
    themeToggleLightIcon.classList.toggle('hidden')
    themeToggleDarkIcon.classList.remove('hidden')
});

const showLightProp = () => {
    var light = document.getElementsByClassName('logo-main-white')
    var dark = document.getElementsByClassName('logo-main-black')
    // show light
    for (let index = 0; index < light.length; index++) {
        let item = light[index]
        item.classList.add('block')
        item.classList.remove('hidden')
    }
    // hide dark
    for (let index = 0; index < dark.length; index++) {
        let item = dark[index]
        item.classList.add('hidden')
        item.classList.remove('block')
    }
}

const showDarkProp = () => {
    var light = document.getElementsByClassName('logo-main-white')
    var dark = document.getElementsByClassName('logo-main-black')
    // show dark
    for (let index = 0; index < dark.length; index++) {
        let item = dark[index]
        item.classList.add('block')
        item.classList.remove('hidden')
    }
    // hide light
    for (let index = 0; index < light.length; index++) {
        let item = light[index]
        item.classList.add('hidden')
        item.classList.remove('block')
    }
}

