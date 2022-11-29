import Process from "./Process";

export default class ProcessManager {

    /**
     * Current index of process
     *
     * @private
     */
    private currentIndex: number

    /**
     * Current process instance
     *
     * @private
     */
    private currentProcess: Process

    /**
     * All processes to be processed
     *
     * @private
     */
    private processes: Process[] = []

    /**
     * Method which is called when all processes have been completed
     */
    private onFinish: Function = () => {}

    /**
     * Adds one or more processes to be queued
     *
     * @param process
     */
    addProcess(...process: Process[]): ProcessManager
    {
        for (const proc of process)
        {
            // Bind manager instance
            proc.addManager(this)

            // Add process to queue
            this.processes.push(proc)
        }

        return this
    }

    /**
     * Starts the execution of all processes
     *
     * @param startIndex
     */
    start(startIndex: number = 0): void
    {
        if(startIndex >= this.processes.length)
        {
            this.exit()
            return
        }

        this.currentIndex = startIndex
        this.currentProcess = this.processes[this.currentIndex]

        this.currentProcess.start()
    }

    /**
     * Call the finish method
     */
    exit(): void
    {
        this.onFinish.call(this)
    }

    /**
     * Starts the next process
     */
    next(): void
    {
        this.start(++this.currentIndex)
    }

    /**
     * Calling the registered method when all processes are finished
     *
     * @param fn
     */
    finish(fn: Function): ProcessManager
    {
        this.onFinish = fn

        return this
    }

    /**
     * Reset manager and all processes
     */
    reset(): void
    {
        this.currentIndex = 0

        for (const proc of this.processes)
        {
            proc.reset()
        }
    }
}
