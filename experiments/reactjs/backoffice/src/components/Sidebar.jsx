import React from 'react';
import { Nav } from 'react-bootstrap';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const Sidebar = () => {
    const { logout } = useAuth();
    const location = useLocation();

    const isActive = (path) => location.pathname === path;

    return (
        <div className="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style={{ width: '280px', minHeight: '100vh' }}>
            <div className="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span className="fs-4">MES Backoffice</span>
            </div>
            <hr />
            <Nav className="flex-column mb-auto">
                <Nav.Item>
                    <Link to="/dashboard" className={`nav-link text-white ${isActive('/dashboard') ? 'active bg-primary' : ''}`}>
                        Dashboard
                    </Link>
                </Nav.Item>

                <div className="mt-3 text-muted text-uppercase small">Factory Assets</div>
                <Nav.Item>
                    <Link to="/machines" className={`nav-link text-white ${isActive('/machines') ? 'active bg-primary' : ''}`}>
                        Machines
                    </Link>
                </Nav.Item>

                 <div className="mt-3 text-muted text-uppercase small">Master Data</div>
                <Nav.Item>
                    <Link to="/articles" className={`nav-link text-white ${isActive('/articles') ? 'active bg-primary' : ''}`}>
                        Articles
                    </Link>
                </Nav.Item>
            </Nav>
            <hr />
            <button onClick={logout} className="btn btn-outline-light w-100">Log Out</button>
        </div>
    );
};

export default Sidebar;