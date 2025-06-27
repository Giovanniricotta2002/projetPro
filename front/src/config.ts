const apiEndpoint = import.meta.env.VITE_BACKEND_URL || 'http://localhost:80';

const corsRequestHeaders = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Access-Control-Request-Method': 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Request-Headers': 'Content-Type, X-CSRF-Token',
}


export {apiEndpoint, corsRequestHeaders};