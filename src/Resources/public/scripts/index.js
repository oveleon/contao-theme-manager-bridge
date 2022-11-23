import "./Import/import"
import "./Export/export"
import Installer from "./Installer/Installer"

const installer = new Installer('de')
const registerProductButton = document.getElementById('registerProduct')

registerProductButton?.addEventListener('click', (e) => {
    e.preventDefault()
    installer.startInstallProcess()
})
