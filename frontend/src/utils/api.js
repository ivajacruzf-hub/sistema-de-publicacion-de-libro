const api_url = "http://api.sistemasdepublicaciondelibros";

const request = async (endpoint, options = {}) => {
    const response = await fetch(`${api_url}/${endpoint.replace(/^\/+/, '')}`, {
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {})
        },
        ...options
    });
    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.error || `Error HTTP ${response.status}`);
    }
    return data;
};

export const api = {
    get: (endpoint) => request(endpoint),
    post: (endpoint, body) => request(endpoint, {
        method: 'POST',
        body: JSON.stringify(body)
    }),
    put: (endpoint, body) => request(endpoint, {
        method: 'PUT',
        body: JSON.stringify(body)
    }),
    delete: (endpoint) => request(endpoint, { method: 'DELETE' })
};