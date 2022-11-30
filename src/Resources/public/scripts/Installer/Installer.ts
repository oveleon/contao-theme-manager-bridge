import Modal from "./components/Modal";
import {setLanguage} from "./lang/";
import {InstallStep, LicenseStep, ProductStep} from "./steps";

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
            new LicenseStep(),
            new ProductStep(),
            new InstallStep()
        )

        this.modal.appendTo('body')
    }

    /**
     * Set current language
     *
     * @param locale
     */
    setLocale(locale: string): void
    {
        this.locale = locale
        setLanguage(locale)
    }

    /**
     * Open Installer
     */
    open(): void
    {
        this.modal.open()
    }
}

export const routes = {
    license: "/contao/installer/check",
    systemcheck: "/contao/installer/install/systemcheck"
}
