import Process, {IProcess} from "./Process"
import {i18n} from "../../lang/"

export default class InstallProcess extends Process implements IProcess
{
    /**
     * @inheritDoc
     */
    getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${i18n('install.install.title')}</div>
                <p>${i18n('install.install.description')}</p>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    process(): void
    {
        setTimeout(() => {
            console.log('Install done')

            this.resolve()
        }, 5500)
    }
}
