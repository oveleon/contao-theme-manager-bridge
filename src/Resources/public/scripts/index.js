import "./Import/import"
import "./Export/export"
import Installer from "./Installer/Installer"

const installer = new Installer('de') // ToDo: set locale
const registerProductButton = document.getElementById('registerProduct')

registerProductButton?.addEventListener('click', (e) => {
    e.preventDefault()
    installer.open()
})
