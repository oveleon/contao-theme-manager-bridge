export default class State
{
    private static state = {}

    public static set(name, value): void
    {
        State.state[name] = value
    }

    public static get(name): any
    {
        return State.state[name]
    }
}
