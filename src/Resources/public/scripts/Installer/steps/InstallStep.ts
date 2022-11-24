import Step from "../components/Step"
import {i18n} from "../lang/"
import ProcessManager, {CheckSystemProcess, InstallProcess} from "./Process";

export default class InstallStep extends Step
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('install.headline')}</h2>
            <div class="process"></div>
            <div class="actions">
                <button class="close primary" disabled>${i18n('product.actions.install')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    events()
    {
        // Get the container in which the processes should be appended
        const container = <HTMLDivElement> this.template.querySelector('.process')

        // Create and start process manager
        new ProcessManager()
            .addProcess(
                new CheckSystemProcess(container),
                new InstallProcess(container)
            )
            .finish(() => {
                console.log('all fin')
            })
            .start()
    }
}
