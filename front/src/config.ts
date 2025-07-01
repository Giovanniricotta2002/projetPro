// const apiEndpoint = import.meta.env.VITE_API_URL || 'http://localhost:80';
// const apiEndpoint = import.meta.env.VITE_API_URL || 'https://backend-api-470976636166.europe-west1.run.app';
const apiEndpoint = 'https://backend-api-470976636166.europe-west1.run.app';

const corsRequestHeaders = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Access-Control-Request-Method': 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Request-Headers': 'Content-Type, X-CSRF-Token',
}


export {apiEndpoint, corsRequestHeaders};