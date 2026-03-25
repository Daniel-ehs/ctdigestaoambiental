
import { AppState, User, ElectricityRecord, WaterRecord, WasteRecord } from '../types';

const API_URL = '/api';

async function request<T>(endpoint: string, options?: RequestInit): Promise<T> {
    const response = await fetch(`${API_URL}${endpoint}`, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...options?.headers,
        },
    });

    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.message || 'Request failed');
    }

    return response.json();
}

export const api = {
    // Auth
    login: (email: string, password: string) => request<User>('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
    }),

    // Users
    getUsers: () => request<User[]>('/users'),
    createUser: (user: Partial<User>) => request<User>('/users', {
        method: 'POST',
        body: JSON.stringify(user),
    }),
    updateUser: (id: string, user: Partial<User>) => request<User>(`/users/${id}`, {
        method: 'PUT',
        body: JSON.stringify(user),
    }),
    deleteUser: (id: string) => request(`/users/${id}`, { method: 'DELETE' }),

    // Settings
    getSettings: () => request<any>('/settings'),
    updateSettings: (settings: any) => request<any>('/settings', {
        method: 'POST', // or PUT, server handles upsert
        body: JSON.stringify(settings),
    }),

    // Electricity
    getElectricity: () => request<ElectricityRecord[]>('/electricity'),
    createElectricity: (data: Partial<ElectricityRecord>) => request<ElectricityRecord>('/electricity', {
        method: 'POST',
        body: JSON.stringify(data),
    }),
    updateElectricity: (id: string, data: Partial<ElectricityRecord>) => request<ElectricityRecord>(`/electricity/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
    }),
    deleteElectricity: (id: string) => request(`/electricity/${id}`, { method: 'DELETE' }),

    // Water
    getWater: () => request<WaterRecord[]>('/water'),
    createWater: (data: Partial<WaterRecord>) => request<WaterRecord>('/water', {
        method: 'POST',
        body: JSON.stringify(data),
    }),
    updateWater: (id: string, data: Partial<WaterRecord>) => request<WaterRecord>(`/water/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
    }),
    deleteWater: (id: string) => request(`/water/${id}`, { method: 'DELETE' }),

    // Waste
    getWaste: () => request<WasteRecord[]>('/waste'),
    createWaste: (data: Partial<WasteRecord>) => {
        // Remove pricePerKg (frontend-only) before sending to backend
        const { pricePerKg, ...rest } = data;
        return request<WasteRecord>('/waste', {
            method: 'POST',
            body: JSON.stringify(rest),
        });
    },
    updateWaste: (id: string, data: Partial<WasteRecord>) => {
        // Remove pricePerKg (frontend-only) before sending to backend
        const { pricePerKg, ...rest } = data;
        return request<WasteRecord>(`/waste/${id}`, {
            method: 'PUT',
            body: JSON.stringify(rest),
        });
    },
    deleteWaste: (id: string) => request(`/waste/${id}`, { method: 'DELETE' }),
};
