import Step from "./Step"
import Container from "./Container"

export default class Modal extends Container
{
    private currentStep: Step
    private currentIndex: number

    private steps: Step[] = []

    constructor(id: string) {
        super(id)
    }

    addSteps(...step: Step[]): void
    {
        for (const s of step)
        {
            this.steps.push(s)
        }
    }

    open(startIndex?: number): void
    {
        if(undefined !== startIndex)
            startIndex = 0

        this.currentIndex = startIndex
        this.currentStep = this.steps[ this.currentIndex ]

        // Close other


        // Show current step
        this.show()
    }
}
