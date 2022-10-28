import tingle from "tingle.js"
import "tingle.js/dist/tingle.css"

var importModal = new tingle.modal({
    footer: true,
    stickyFooter: false,
    closeMethods: ['escape'],
    cssClass: ['theme-assistant-modal']
});

importModal.addFooterBtn('Abbrechen', 'tl_submit', function() {
    importModal.close()
});

importModal.addFooterBtn('Importieren', 'tl_submit', function() {
    importModal.close()
});

for(let button of document.querySelectorAll('.assistant-module .module a.import'))
{
    button.addEventListener('click', (e) => {
        e.preventDefault()

        importModal.setContent('Theme importing is currently under development.')
        importModal.open()
    })
}

var exportModal = new tingle.modal({
    footer: true,
    stickyFooter: false,
    closeMethods: ['escape'],
    cssClass: ['theme-assistant-modal']
});

exportModal.addFooterBtn('Abbrechen', 'tl_submit', function() {
    exportModal.close()
});

exportModal.addFooterBtn('Exportieren', 'tl_submit', function() {
    const form = exportModal.modalBoxContent.querySelector('form');

    if(!form.checkValidity())
    {
        form.reportValidity()
        return;
    }

    form.submit()
    exportModal.close()
});

document.querySelector('#theme-assistant-buttons a.content-export').addEventListener('click', (e) => {
    e.preventDefault()

    exportModal.setContent(createExportTemplate())
    exportModal.open()
})

function createExportTemplate(){
    const template = document.querySelector("#content-export-template").content.cloneNode(true)

    //

    return template
}
