import React, { createContext, useContext, useState } from 'react';
import api from '../lib/axios';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [apiKey, setApiKey] = useState(localStorage.getItem('mes_api_key'));

    const login = async (username, password) => {
        try {
            const response = await api.post('/login_json.php', { username, password });
            if (response.data.status === 'success') {
                const { api_key, user } = response.data;
                localStorage.setItem('mes_api_key', api_key);
                setApiKey(api_key);
                setUser(user);
                return { success: true };
            } else {
                return { success: false, message: response.data.message };
            }
        } catch (error) {
            console.error("Login error", error);
            return { success: false, message: 'Network or server error' };
        }
    };

    const logout = () => {
        localStorage.removeItem('mes_api_key');
        setApiKey(null);
        setUser(null);
    };

    return (
        <AuthContext.Provider value={{ user, apiKey, login, logout }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);