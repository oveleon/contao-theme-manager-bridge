import Container from "./Container"

export default class Step extends Container
{
    static stepId: number

    constructor() {
        // Auto-increment id
        Step.stepId++

        // Create container
        super('step' + Step.stepId)

        // Steps are hidden by default
        this.template.hidden = true
    }


}
