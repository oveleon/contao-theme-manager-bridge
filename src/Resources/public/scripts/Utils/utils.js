export function getTemplate(selector){
    return document.querySelector(selector).content.cloneNode(true)
}
