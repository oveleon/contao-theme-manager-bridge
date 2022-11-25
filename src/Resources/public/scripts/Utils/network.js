export async function call(url, parameter = {})
{
    const props = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(parameter)
    }

    return fetch(url, props)
            .then((response) => response.json())
            .then((data) => data)
}
