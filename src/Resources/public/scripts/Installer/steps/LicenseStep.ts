import Step from "../components/Step"
import {i18n} from "../lang/"

export default class LicenseStep extends Step
{
    setTemplate(): void
    {
        const content = `
            <h2>${i18n('license.headline')}</h2>
            <p>${i18n('license.description')}</p>
            <form>
                <div class="widget">
                    <label for="license">${i18n('license.form.label.license')}</label>
                    <input name="license" id="license" />
                </div>
            </form>
            <div class="actions">
                <button class="check">${i18n('license.actions.next')}</button>
            </div>
        `

        this.content(content)
        this.bindEvents()
    }

    bindEvents()
    {
        this.template.querySelector('.check').addEventListener('click', () => {
            // ToDo: Check license

            // Check license and goto next step on success
            this.modal.next()
        })
    }
}
