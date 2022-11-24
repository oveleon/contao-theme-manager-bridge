import Container from "./Container"
import Modal from "./Modal";

interface StepErrorResponse {
    error: number,
    fields?: []
}

export default abstract class Step extends Container
{
    static stepId: number = 0
    private init: boolean = false
    protected modal: Modal

    constructor() {
        // Auto-increment id
        Step.stepId++

        // Create container
        super('step' + Step.stepId)

        // Steps are hidden by default
        this.hide()
    }

    addModal(modal: Modal): void
    {
        this.modal = modal
    }

    show(): void
    {
        if(!this.init)
        {
            // Update content before show
            super.content(this.getTemplate())

            // Bind default events
            this.defaultEvents()

            // Bind custom events
            this.events()

            // Run only the first time
            this.init = true
        }

        // Show step
        super.show()
    }

    /**
     * Register default events
     */
    defaultEvents(): void
    {
        this.template.querySelector('[data-close]')?.addEventListener('click', () => this.modal.hide())
        this.template.querySelector('[data-prev]')?.addEventListener('click', () => this.modal.prev())
        this.template.querySelector('[data-next]')?.addEventListener('click', () => this.modal.next())
    }

    /**
     * Handle errors
     *
     * @param response
     */
    error(response: StepErrorResponse): void
    {
        if(response?.fields)
        {
            const form = <HTMLFormElement> this.template.querySelector('form')

            for(const f in response.fields)
            {
                // Add error css class
                form[f].parentElement.classList.add('error')

                // Check if the field already has an error text
                if(form[f].nextElementSibling)
                {
                    // Change error text
                    form[f].nextElementSibling.innerHTML = response.fields[f]
                }else{
                    // Add error text
                    const errorText = document.createElement('p')
                    errorText.innerHTML = response.fields[f]

                    form[f].after(errorText)
                }

                // Add event
                form[f].addEventListener('input', () => {
                    form[f].parentElement.classList.remove('error')
                }, {once: true})
            }
        }
    }

    /**
     * Set template events
     *
     * @protected
     */
    protected events(): void {}

    /**
     * Get template structure
     *
     * @protected
     */
    protected abstract getTemplate(): string
}
