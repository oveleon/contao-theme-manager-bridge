import Container from "./Container"
import Modal from "./Modal";

export default abstract class Step extends Container
{
    static stepId: number = 0

    constructor(
        protected modal: Modal
    ) {
        // Auto-increment id
        Step.stepId++

        // Create container
        super('step' + Step.stepId)

        // Steps are hidden by default
        this.hide()

        // Set step template (content)
        this.content(this.getTemplate())
    }

    content(html: string): void
    {
        super.content(html)

        // Bind default events
        this.defaultEvents()

        // Bind custom events
        this.events()
    }

    defaultEvents(): void
    {
        this.template.querySelector('[data-close]')?.addEventListener('click', () => this.modal.hide())
        this.template.querySelector('[data-prev]')?.addEventListener('click', () => this.modal.prev())
    }

    protected events(): void {}

    protected abstract getTemplate(): string
}
