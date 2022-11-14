import Modal from "./components/Modal";

export default class Installer {
    private modal: Modal

    constructor() {
        this.modal = new Modal('installer')
    }

    startInstallProzess() {
        this.modal.show()
    }
}
