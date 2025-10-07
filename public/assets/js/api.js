// public/assets/js/api.js

const basePath = '/iCensus-ent/public';

export async function fetchData(endpoint, params = {}) {
    try {
        const url = new URL(`${basePath}/${endpoint}`, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error(`Failed to fetch from ${endpoint}:`, error);
        return { error: `Could not load data from ${endpoint}.` };
    }
}