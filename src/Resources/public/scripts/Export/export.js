import tingle from "tingle.js"
import "tingle.js/dist/tingle.css"
import {getTemplate} from "../Utils/utils";

var exportModal = new tingle.modal({
    footer: true,
    stickyFooter: false,
    closeMethods: ['escape'],
    cssClass: ['theme-assistant-modal']
});

exportModal.addFooterBtn('Abbrechen', 'tl_submit', () => {
    exportModal.close()
});

exportModal.addFooterBtn('Exportieren', 'tl_submit', () => {
    const form = exportModal.modalBoxContent.querySelector('form')

    if(!form.checkValidity())
    {
        form.reportValidity()
        return
    }

    form.submit()
    exportModal.close()
})

document.querySelector('#theme-assistant-buttons a.content-export').addEventListener('click', (e) => {
    e.preventDefault()

    exportModal.setContent(getTemplate("#content-export-template"))
    exportModal.open()
})
