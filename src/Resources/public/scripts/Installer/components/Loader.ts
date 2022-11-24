import Container from "./Container"
import Modal from "./Modal";

export enum LoaderMode {
    DISABLED = 'disabled',
    INLINE= 'inline',
    COVER = 'cover'
}

export default class Loader extends Container
{
    static loaderId: number = 0

    private readonly spinnerContainer: HTMLDivElement
    private readonly textContainer: HTMLParagraphElement

    constructor() {
        // Auto-increment id
        Loader.loaderId++

        // Create container
        super('loader' + Loader.loaderId)

        // Add template attributes
        this.template.classList.add('loader')

        // Create content
        this.spinnerContainer = <HTMLDivElement> document.createElement('div')
        this.spinnerContainer.classList.add('spinner')
        this.spinnerContainer.innerHTML = `
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        `
        this.textContainer = <HTMLParagraphElement> document.createElement('p')
        this.textContainer.classList.add('text')

        this.template.append(this.spinnerContainer)
        this.template.append(this.textContainer)

        // Loader defaults
        this.hide()
        this.play()
        this.setMode(LoaderMode.INLINE)
    }

    setMode(type: LoaderMode)
    {
        this.template.classList.remove(
            LoaderMode.INLINE,
            LoaderMode.COVER
        )

        this.template.classList.add(type)
    }

    setText(text: string): void
    {
        this.textContainer.innerHTML = text
    }

    hide(): void
    {
        this.setText('')
        super.hide()
    }

    play(): void
    {
        this.template.classList.add('play')
    }

    pause(): void
    {
        this.template.classList.remove('play')
    }
}
