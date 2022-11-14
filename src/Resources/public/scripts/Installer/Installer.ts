import Modal from "./components/Modal";
import {setLanguage} from "./lang/";
import LicenseStep from "./steps/LicenseStep";

export default class Installer
{
    private locale: string
    private modal: Modal

    constructor(locale: string)
    {
        // Set current locale
        this.setLocale(locale)

        // Create modal and steps
        this.modal = new Modal('installer')
        this.modal.addSteps(
            new LicenseStep(this.modal)
        )
    }

    setLocale(locale: string): void
    {
        this.locale = locale
        setLanguage(locale)
    }

    startInstallProcess(): void
    {
        this.modal.open()
    }
}
