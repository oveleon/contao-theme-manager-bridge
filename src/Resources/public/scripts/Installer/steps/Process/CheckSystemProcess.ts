import Process, {IProcess} from "./Process";
import {i18n} from "../../lang/"

export default class CheckSystemProcess extends Process implements IProcess
{
    /**
     * @inheritDoc
     */
    getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${i18n('install.systemcheck.title')}</div>
                <p>${i18n('install.systemcheck.description')}</p>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    process(): void
    {
        setTimeout(() => {
            console.log('System check done')

            this.resolve()
        }, 2000)
    }
}
