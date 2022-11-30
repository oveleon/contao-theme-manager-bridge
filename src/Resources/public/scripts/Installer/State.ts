export default class State
{
    private static state = {}

    public static set(name: string, value: any): void
    {
        State.state[name] = value
    }

    public static get(name): any
    {
        return State.state[name]
    }

    public static clear(name?: string): void
    {
        if(name)
        {
            delete State.state[name]
            return
        }

        State.state = {}
    }
}
