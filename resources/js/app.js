import './bootstrap'
import './theme'
import '@fortawesome/fontawesome-free/css/fontawesome.css'
import '@fortawesome/fontawesome-free/css/brands.css'
import '@fortawesome/fontawesome-free/css/solid.css'
import Alpine from 'alpinejs'
import { themeChange } from 'theme-change'

window.Alpine = Alpine

Alpine.start()

themeChange()
