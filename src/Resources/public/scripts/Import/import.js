import tingle from "tingle.js"
import "tingle.js/dist/tingle.css"
import {getTemplate} from "../Utils/dom";

/**
 * Import content package
 */
var importModal = new tingle.modal({
    footer: true,
    stickyFooter: false,
    closeMethods: ['escape'],
    cssClass: ['theme-assistant-modal']
})

importModal.addFooterBtn('Abbrechen', 'tl_submit', () => {
    importModal.close()
})

importModal.addFooterBtn('Importieren', 'tl_submit', () => {
    const form = importModal.modalBoxContent.querySelector('form')

    if(!form.checkValidity())
    {
        form.reportValidity()
        return
    }

    form.submit()
    importModal.close()
})

for(let button of document.querySelectorAll('.assistant-module a.import'))
{
    button.addEventListener('click', (e) => {
        e.preventDefault()

        importModal.setContent(getTemplate('#' + button.dataset.template))
        importModal.open()
    })
}
