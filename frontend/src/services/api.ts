import axios from 'axios';

const API_URL = 'http://localhost:8000/api/properties';

export const getProperties = async () => {
    return await axios.get(API_URL);
};

export const getProperty = async (id: number) => {
    return await axios.get(`${API_URL}/${id}`);
};

export const addProperty = async (property: { name: string; type: string; parent_id?: number }) => {
    return await axios.post(API_URL, property);
};

export const deleteProperty = async (id: number) => {
    return await axios.delete(`${API_URL}/${id}`);
};
