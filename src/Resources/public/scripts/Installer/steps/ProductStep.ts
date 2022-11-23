import Step from "../components/Step"
import {i18n} from "../lang/"

export default class ProductStep extends Step
{
    getTemplate(): string
    {
        return `
            <h2>Ihr Produkt</h2>
            <p>Produkt gefunden</p>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
                <button class="install primary">${i18n('product.actions.install')}</button>
            </div>
        `
    }

    events()
    {
        this.template.querySelector('.install').addEventListener('click', () => {
            // ToDo: validate

            // Check license and goto next step on success
            this.modal.next()
        })
    }
}
