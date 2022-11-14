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
        this.template.hidden = true

        // Set step template (content)
        this.setTemplate()
    }

    abstract setTemplate(): void
}
