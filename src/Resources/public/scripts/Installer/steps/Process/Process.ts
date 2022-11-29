import ProcessManager from "./ProcessManager"
import Loader from "../../components/Loader";
import Container from "../../components/Container";

export interface ProcessErrorResponse {
    error: number | boolean,
    messages?: string[]
}

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
        this.loader?.removeClass('done', 'fail', 'pause')
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

    /**
     * Resolve process
     */
    resolve(): void
    {
        this.loader?.pause()
        this.loader?.addClass('done')

        // Start next process
        this.manager.next()
    }

    /**
     * Reject process
     *
     * @param data
     */
    reject(data: Error | ProcessErrorResponse): void
    {
        this.loader?.pause()
        this.loader?.addClass('fail')

        this.error(data)
    }

    /**
     * Shows occurred errors in the process
     */
    error(data: any): void
    {
        // ToDo: Show Error messages

        // Create error container
        const errors = <HTMLDivElement> document.createElement('div')

        errors.classList.add('errors')
        this.template.append(errors)

        // Check for messages of intercepted errors
        if(data?.messages)
        {
            for (const text of data.messages)
            {
                const msg = <HTMLParagraphElement> document.createElement('p')
                msg.innerText = text
                errors.append(msg)
            }
        }

        // Check whether a fatal error has occurred.
        // For example, no connection could be established to the server
        if(data?.message)
        {
            const msg = <HTMLParagraphElement> document.createElement('p')
            msg.innerText = data.message
            errors.append(msg)
        }
    }

    /**
     * Pause process
     */
    pause(): void
    {
        this.loader?.pause()
        this.loader?.addClass('pause')
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
