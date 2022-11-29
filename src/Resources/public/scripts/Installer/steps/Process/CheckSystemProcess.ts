import Process, {IProcess, ProcessErrorResponse} from "./Process"
import {i18n} from "../../lang/"
import {routes} from "../../Installer"
import {call} from "../../../Utils/network"

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
        // Check license
        call(routes.systemcheck).then((response) => {

            console.log(response)

            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            this.resolve()
        }).catch((e: Error) => this.reject(e))
    }

    /**
     * @inheritDoc
     */
    reject(data: Error | ProcessErrorResponse): void
    {
        super.reject(data);

        // Exit manager and following processes
        this.manager.exit()
    }
}
