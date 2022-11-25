import Step from "../components/Step"
import {i18n} from "../lang/"
import State from "../State";
import {call} from "../../Utils/network"
import {routes} from "../Installer";

export default class LicenseStep extends Step
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('license.headline')}</h2>
            <p>${i18n('license.description')}</p>
            <form id="license-form" autocomplete="off">
                <div class="widget">
                    <label for="license">${i18n('license.form.label.license')}</label>
                    <input type="text" name="license" id="license" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" autocomplete="off" required/>
                </div>
            </form>
            <div class="actions">
                <button data-close>${i18n('actions.close')}</button>
                <button type="submit" form="license-form" class="check primary">${i18n('license.actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    submit(form: HTMLFormElement, data: FormData)
    {
        // Save license form data
        State.set('license', data.get('license'))

        // Show loader
        this.modal.loader(true, i18n('license.loading'))

        // Check license
        call(routes.license, {
            license: data.get('license')
        }).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            // Save product information
            State.set('product', response)

            // Reset form
            form.reset()

            // Unlock form
            this.lockedForm = false

            // Show next step
            this.modal.next()
        }).catch(() => {
            // ToDo: Error
            console.log('error catch')

            // Unlock form
            this.lockedForm = false
        })
    }
}
