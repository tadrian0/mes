import React, { useEffect, useState } from 'react';
import api from '../lib/axios';
import { Table, Button, Modal, Form, Alert } from 'react-bootstrap';

const Articles = () => {
    const [articles, setArticles] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [currentArticle, setCurrentArticle] = useState(null);
    const [formData, setFormData] = useState({ name: '', description: '', quality_control: 'Pending' });
    const [error, setError] = useState('');

    const fetchArticles = async () => {
        try {
            const response = await api.get('/article.php?action=list');
            if (response.data.status === 'success') {
                setArticles(response.data.data);
            }
        } catch (err) {
            setError('Failed to fetch articles');
            console.error(err);
        }
    };

    useEffect(() => {
        fetchArticles();
    }, []);

    const handleShow = (article = null) => {
        if (article) {
            setCurrentArticle(article);
            setFormData({
                name: article.Name,
                description: article.Description || '',
                quality_control: article.QualityControl || 'Pending'
            });
        } else {
            setCurrentArticle(null);
            setFormData({ name: '', description: '', quality_control: 'Pending' });
        }
        setShowModal(true);
    };

    const handleClose = () => setShowModal(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        const data = new FormData();
        data.append('name', formData.name);
        data.append('description', formData.description);
        data.append('quality_control', formData.quality_control);

        try {
            if (currentArticle) {
                data.append('id', currentArticle.ArticleID);
                await api.post('/article.php?action=update', data);
            } else {
                await api.post('/article.php?action=create', data);
            }
            fetchArticles();
            handleClose();
        } catch (err) {
            setError('Operation failed');
            console.error(err);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm('Are you sure?')) {
            const data = new FormData();
            data.append('id', id);
            await api.post('/article.php?action=delete', data);
            fetchArticles();
        }
    };

    return (
        <div>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h1>Articles</h1>
                <Button variant="primary" onClick={() => handleShow()}>Add Article</Button>
            </div>
            {error && <Alert variant="danger">{error}</Alert>}
            <Table striped bordered hover>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>QC Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {articles.map(article => (
                        <tr key={article.ArticleID}>
                            <td>{article.ArticleID}</td>
                            <td>{article.Name}</td>
                            <td>{article.Description}</td>
                            <td>{article.QualityControl}</td>
                            <td>
                                <Button variant="outline-primary" size="sm" className="me-2" onClick={() => handleShow(article)}>Edit</Button>
                                <Button variant="outline-danger" size="sm" onClick={() => handleDelete(article.ArticleID)}>Delete</Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </Table>

            <Modal show={showModal} onHide={handleClose}>
                <Modal.Header closeButton>
                    <Modal.Title>{currentArticle ? 'Edit Article' : 'Add Article'}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <Form onSubmit={handleSubmit}>
                        <Form.Group className="mb-3">
                            <Form.Label>Name</Form.Label>
                            <Form.Control
                                type="text"
                                value={formData.name}
                                onChange={(e) => setFormData({...formData, name: e.target.value})}
                                required
                            />
                        </Form.Group>
                        <Form.Group className="mb-3">
                            <Form.Label>Description</Form.Label>
                            <Form.Control
                                as="textarea"
                                value={formData.description}
                                onChange={(e) => setFormData({...formData, description: e.target.value})}
                            />
                        </Form.Group>
                        <Form.Group className="mb-3">
                            <Form.Label>Quality Control</Form.Label>
                            <Form.Select
                                value={formData.quality_control}
                                onChange={(e) => setFormData({...formData, quality_control: e.target.value})}
                            >
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </Form.Select>
                        </Form.Group>
                        <Button variant="primary" type="submit">Save</Button>
                    </Form>
                </Modal.Body>
            </Modal>
        </div>
    );
};

export default Articles;