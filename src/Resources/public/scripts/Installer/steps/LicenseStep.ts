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
    events()
    {
        this.template.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault()

            const form = <HTMLFormElement> e.target
            const data = new FormData(form)

            if(!form.checkValidity())
            {
                form.reportValidity()
                return;
            }

            // Save license form data
            State.set('license', data.get('license'));

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
                State.set('product', response);

                // Show next step
                this.modal.next()
            })
        })
    }
}
