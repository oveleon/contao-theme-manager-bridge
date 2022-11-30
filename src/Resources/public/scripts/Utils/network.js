export async function call(url, parameter = {}, cache = false)
{
    const props = {
        method: 'POST',
        cache: cache ? "force-cache" : "no-cache",
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(parameter)
    }

    return fetch(url, props)
            .then((response) => response.json())
            .then((data) => data)
}
