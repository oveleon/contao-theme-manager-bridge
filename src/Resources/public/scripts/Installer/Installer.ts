import Modal from "./components/Modal";
import {setLanguage} from "./lang/";
import LicenseStep from "./steps/LicenseStep";
import ProductStep from "./steps/ProductStep";

export default class Installer
{
    private locale: string
    private readonly modal: Modal

    constructor(locale: string)
    {
        // Set current locale
        this.setLocale(locale)

        // Create modal and steps
        this.modal = new Modal('installer')
        this.modal.addSteps(
            new LicenseStep(this.modal),
            new ProductStep(this.modal)
        )

        this.modal.appendTo('body')
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
