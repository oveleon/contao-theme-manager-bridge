export default class Container
{
    public template: HTMLDivElement

    constructor(
        public id: string
    ){
        this.create()
    }

    create(): void
    {
        this.template = <HTMLDivElement>document.createElement('div')
        this.template.id = this.id
    }

    show(): void
    {

    }
}
