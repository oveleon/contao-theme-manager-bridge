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
        this.template = <HTMLDivElement>document.createElement('div')
        this.template.id = this.id
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
}
