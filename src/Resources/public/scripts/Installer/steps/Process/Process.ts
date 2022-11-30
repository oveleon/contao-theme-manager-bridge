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
    protected errorContainer: HTMLDivElement

    constructor(
        protected container: HTMLElement
    ){
        // Create container
        super('process' + Process.processId++)

        // Create process step template
        this.addClass('process-step', 'not-active')
        this.content(this.getTemplate())
        this.appendTo(this.container)

        // Add error message container
        this.errorContainer = <HTMLDivElement> document.createElement('div')
        this.errorContainer.classList.add('errors')
        this.template.append(this.errorContainer)

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

        this.template.querySelector('div.errors')?.remove()
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
        // Check for messages of intercepted errors
        if(data?.messages)
        {
            for (const text of data.messages)
            {
                this.addErrorParagraph(text)
            }
        }

        // Check whether a fatal error has occurred.
        // For example, no connection could be established to the server
        if(data?.message)
        {
            this.addErrorParagraph(data.message)
        }
    }

    /**
     * Adds a paragraph to the error container
     *
     * @param content
     */
    addErrorParagraph(content: string): void
    {
        const msg = <HTMLParagraphElement> document.createElement('p')
        msg.innerText = content
        this.errorContainer.append(msg)
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
