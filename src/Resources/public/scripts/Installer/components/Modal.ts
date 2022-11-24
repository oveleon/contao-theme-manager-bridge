import Step from "./Step"
import Container from "./Container"
import Loader, {LoaderMode} from "./Loader";

export default class Modal extends Container
{
    private currentStep: Step
    private currentIndex: number

    private readonly insideContainer: HTMLDivElement
    private readonly stepContainer: HTMLDivElement
    private readonly closeElement: HTMLDivElement // ToDo:
    private readonly loaderElement: Loader
    private steps: Step[] = []

    constructor(id: string) {
        super(id)

        // Hide modal by default
        this.hide()

        // Create inside container
        this.insideContainer = <HTMLDivElement> document.createElement('div')
        this.insideContainer.classList.add('inside')

        this.template.append(this.insideContainer)

        // Create step container
        this.stepContainer = <HTMLDivElement> document.createElement('div')
        this.stepContainer.id = 'steps'

        this.insideContainer.append(this.stepContainer)

        // Create loader
        this.loaderElement = new Loader()
        this.loaderElement.setMode(LoaderMode.COVER)
        this.loaderElement.appendTo(this.insideContainer)
    }

    addSteps(...step: Step[]): void
    {
        for (const s of step)
        {
            s.addModal(this)

            this.steps.push(s)

            s.appendTo(this.stepContainer)
        }
    }

    open(startIndex: number = 0): void
    {
        this.currentIndex = startIndex
        this.currentStep = this.steps[ this.currentIndex ]

        // Close other
        this.closeSteps()

        // Show current step
        this.currentStep.show()

        // Show modal
        this.show()
    }

    loader(state: boolean = true, text?: string): void
    {
        state ?
            this.loaderElement.show() :
            this.loaderElement.hide()

        text ?
            this.loaderElement.setText(text) :
            this.loaderElement.setText('')
    }

    next(): void
    {
        this.open(++this.currentIndex)
    }

    prev(): void
    {
        this.open(--this.currentIndex)
    }

    closeSteps(): void
    {
        for (const step of this.steps)
        {
            step.hide()
        }
    }
}
