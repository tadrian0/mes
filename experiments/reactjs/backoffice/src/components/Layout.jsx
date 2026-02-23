import React from 'react';
import Sidebar from './Sidebar';
import { Outlet, Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const Layout = () => {
    const { apiKey } = useAuth();

    if (!apiKey) {
        return <Navigate to="/login" />;
    }

    return (
        <div className="d-flex">
            <Sidebar />
            <div className="flex-grow-1 p-4" style={{ overflowY: 'auto', height: '100vh', backgroundColor: '#f8f9fa' }}>
                <Outlet />
            </div>
        </div>
    );
};

export default Layout;