import Step from "./Step"
import Container from "./Container"

export default class Modal extends Container
{
    private currentStep: Step
    private currentIndex: number

    private steps: Step[] = []

    constructor(id: string) {
        super(id)

        // ToDo: Create modal content
    }

    addSteps(...step: Step[]): void
    {
        for (const s of step)
        {
            this.steps.push(s)
            // ToDo: Append steps to modal content
        }
    }

    open(startIndex?: number): void
    {
        if(undefined !== startIndex)
            startIndex = 0

        this.currentIndex = startIndex
        this.currentStep = this.steps[ this.currentIndex ]

        // Close other
        this.closeAll()

        // Show current step
        this.currentStep.show()

        // Show modal
        this.show()
    }

    next(): void
    {
        this.open(this.currentIndex++)
    }

    closeAll(): void
    {
        for (const step of this.steps)
        {
            step.hide()
        }
    }
}
