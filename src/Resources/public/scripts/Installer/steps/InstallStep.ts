import Step from "../components/Step"
import {i18n} from "../lang/"
import ProcessManager, {CheckSystemProcess, InstallProcess} from "./Process"
import RegisterProcess from "./Process/RegisterProcess"

export default class InstallStep extends Step
{
    private manager: ProcessManager

    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('install.headline')}</h2>
            <div class="process"></div>
            <div class="actions">
                <button data-close disabled>${i18n('actions.close')}</button>
                <button class="add primary" disabled>${i18n('install.actions.add')}</button>
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

        const addButton = <HTMLButtonElement> this.template.querySelector('button.add')
        const closeButton = <HTMLButtonElement> this.template.querySelector('[data-close]')

        // Method for reset the step
        const resetProcess = () => {
            addButton.disabled = true
            closeButton.disabled = true

            this.manager.reset()
        }

        // Create process manager
        this.manager = new ProcessManager()

        // Add processes
        this.manager.addProcess(
            new CheckSystemProcess(container),
            new RegisterProcess(container),
            new InstallProcess(container)
        )

        // Register on finish method
        this.manager.finish(() => {
            addButton.disabled = false
            closeButton.disabled = false

            closeButton.addEventListener('click', () => {
                // Reset all
                resetProcess()

                this.modal.hide()
            })

            addButton.addEventListener('click', () => {
                // Reset all
                resetProcess()

                this.modal.open(0)
            })
        })

        // Start process manager
        this.manager.start()
    }
}
