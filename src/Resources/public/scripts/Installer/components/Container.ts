export default class Container
{
    public template: HTMLDivElement

    constructor(
        public id: string
    ){
        this.create()
    }

    private create(): void
    {
        this.template = <HTMLDivElement> document.createElement('div')
        this.template.id = this.id
    }

    appendTo(target: string | HTMLElement): void
    {
        if(target instanceof HTMLElement)
        {
            target.append(this.template)
            return;
        }

        document.querySelector(target).append(this.template)
    }

    content(html: string): void
    {
        this.template.innerHTML = html
    }

    hide(): void
    {
        this.template.hidden = true
    }

    show(): void
    {
        this.template.hidden = false
    }

    addClass(...className: string[]): void
    {
        this.template.classList.add(...className)
    }

    removeClass(...className: string[]): void
    {
        this.template.classList.remove(...className)
    }
}
