import ProcessManager from "./ProcessManager"
import Loader from "../../components/Loader";
import Container from "../../components/Container";

export interface IProcess
{
    process(): void
    mount(): void
    getTemplate(): string
}

export default abstract class Process extends Container implements IProcess
{
    static processId: number = 0
    protected loader: Loader

    constructor(
        protected container: HTMLElement
    ){
        // Create container
        super('process' + Process.processId++)

        // Create process step template
        this.addClass('process-step', 'not-active')
        this.content(this.getTemplate())
        this.appendTo(this.container)

        // Add loader
        const loaderContainer = <HTMLDivElement> this.template.querySelector('[data-loader]')

        if(loaderContainer)
        {
            this.loader = new Loader()
            this.loader.show()
            this.loader.pause()
            this.loader.appendTo(loaderContainer)
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
     * Reset process
     */
    reset(): void
    {
        this.addClass('not-active')

        this.loader?.pause()
        this.loader?.removeClass('done', 'fail')
    }

    /**
     * Starts a single process
     */
    start(): void
    {
        this.loader?.play()
        this.removeClass('not-active')

        // Start process
        this.process()
    }

    resolve(): void
    {
        this.loader?.pause()
        this.loader?.addClass('done')

        // Start next process
        this.manager.next()
    }

    reject(): void
    {
        this.loader?.pause()
        this.loader?.addClass('fail')
    }

    /**
     * Allows manipulation for process specific properties
     */
    mount(): void {}

    /**
     * Start the process
     */
    abstract process(): void

    /**
     * Template for process step
     */
    abstract getTemplate(): string
}
