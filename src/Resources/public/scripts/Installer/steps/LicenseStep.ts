import Step from "../components/Step"
import {i18n} from "../lang/"

export default class LicenseStep extends Step
{
    getTemplate(): string
    {
        return `
            <h2>${i18n('license.headline')}</h2>
            <p>${i18n('license.description')}</p>
            <form id="license-form">
                <div class="widget">
                    <label for="license">${i18n('license.form.label.license')}</label>
                    <input type="text" name="license" id="license" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" required/>
                </div>
            </form>
            <div class="actions">
                <button data-close>${i18n('actions.close')}</button>
                <button type="submit" form="license-form" class="check primary">${i18n('license.actions.next')}</button>
            </div>
        `
    }

    events()
    {
        this.template.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault()

            const form = <HTMLFormElement> e.target

            if(!form.checkValidity())
            {
                form.reportValidity()
                return;
            }

            this.modal.loader(true, 'Produkte werden abgerufen')

            // ToDo: Check license and validate

            setTimeout(() => {
                this.modal.loader(false)
                this.modal.next()

                // ToDo: Create globale states to save information
            }, 4000)

        })
    }
}
