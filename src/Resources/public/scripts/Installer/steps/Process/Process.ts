import ProcessManager from "./ProcessManager"
import Loader, {LoaderMode} from "../../components/Loader";

export interface IProcess
{
    start(): void
    mount(): void
    getTemplate(): string
}

export default abstract class Process implements IProcess
{
    protected template: HTMLDivElement
    protected loader: Loader

    constructor(
        protected container: HTMLElement
    ){
        // Create process step template
        this.template = document.createElement('div')
        this.template.classList.add('process-step')
        this.template.innerHTML = this.getTemplate()

        this.container.append(this.template)

        // Add loader
        if(this.template.querySelector('[data-loader]'))
        {
            this.loader = new Loader()
            this.loader.show()
            this.loader.pause()
            this.loader.appendTo(<HTMLDivElement> this.template.querySelector('[data-loader]'))
        }

        this.mount()
    }

    /**
     * The manager instance
     *
     * @protected
     */
    protected manager: ProcessManager

    /**
     * Bind a manager instance to a process step
     *
     * @param manager
     */
    addManager(manager: ProcessManager): void
    {
        this.manager = manager
    }

    /**
     * Allows manipulation for process specific properties
     */
    mount(): void {}

    /**
     * Starts a single process
     */
    abstract start(): void

    /**
     * Template for process step
     */
    abstract getTemplate(): string
}
