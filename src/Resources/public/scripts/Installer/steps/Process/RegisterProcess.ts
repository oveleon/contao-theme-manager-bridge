import Process, {IProcess} from "./Process";
import {i18n} from "../../lang/"

export default class RegisterProcess extends Process implements IProcess
{
    /**
     * @inheritDoc
     */
    getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${i18n('install.register.title')}</div>
                <p>${i18n('install.register.description')}</p>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    process(): void
    {
        setTimeout(() => {
            console.log('Product registration done')

            this.resolve()
        }, 3000)
    }
}
