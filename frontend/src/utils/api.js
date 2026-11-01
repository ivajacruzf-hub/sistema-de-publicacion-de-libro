const configuredBaseUrl = document.querySelector('meta[name="api-base-url"]')?.content;
const apiUrl = (window.API_BASE_URL || configuredBaseUrl || 'http://api.sistemasdepublicaciondelibros').replace(/\/+$/, '');

const getAccessToken = () => {
    const storedSession = localStorage.getItem('publicacion-user') || sessionStorage.getItem('publicacion-user');
    if (!storedSession) return null;
    try {
        return JSON.parse(storedSession).token || null;
    } catch {
        return null;
    }
};

const request = async (endpoint, options = {}) => {
    const token = getAccessToken();
    const response = await fetch(`${apiUrl}/${endpoint.replace(/^\/+/, '')}`, {
        headers: {
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(options.headers || {})
        },
        ...options
    });
    const responseText = await response.text();
    let data;
    try {
        data = responseText ? JSON.parse(responseText) : null;
    } catch {
        data = null;
    }
    if (!response.ok) {
        throw new Error(data?.error || data?.message || `Error HTTP ${response.status}`);
    }
    if (data === null) {
        throw new Error('La API devolvió una respuesta vacía o no válida');
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