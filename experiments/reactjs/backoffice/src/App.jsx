import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Articles from './pages/Articles';
import Machines from './pages/Machines';
import Layout from './components/Layout';
import { AuthProvider } from './context/AuthContext';
import 'bootstrap/dist/css/bootstrap.min.css';

function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/" element={<Layout />}>
           <Route index element={<Navigate to="/dashboard" />} />
           <Route path="dashboard" element={<Dashboard />} />
           <Route path="articles" element={<Articles />} />
           <Route path="machines" element={<Machines />} />
        </Route>
      </Routes>
    </AuthProvider>
  );
}

export default App;