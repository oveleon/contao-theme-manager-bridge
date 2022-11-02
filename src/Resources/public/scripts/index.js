import tingle from "tingle.js"
import "tingle.js/dist/tingle.css"

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
    exportModal.close()
})

for(let button of document.querySelectorAll('.assistant-module a.import'))
{
    button.addEventListener('click', (e) => {
        e.preventDefault()

        importModal.setContent(getTemplate('#' + button.dataset.template))
        importModal.open()
    })
}

/**
 * Export content package
 */
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

function getTemplate(selector){
    return document.querySelector(selector).content.cloneNode(true)
}
