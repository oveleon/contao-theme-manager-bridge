import Process, {IProcess} from "./Process";

export default class InstallProcess extends Process implements IProcess
{
    /**
     * @inheritDoc
     */
    getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    start(): void
    {
        setTimeout(() => {
            console.log('Install done')
            this.manager.next()
        }, 2000)
    }
}
