import React, { useEffect, useState } from 'react';
import api from '../lib/axios';
import { Table, Button, Modal, Form, Alert, Badge } from 'react-bootstrap';

const Machines = () => {
    const [machines, setMachines] = useState([]);
    const [plants, setPlants] = useState([]);
    const [sections, setSections] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [currentMachine, setCurrentMachine] = useState(null);
    const [formData, setFormData] = useState({
        name: '', status: 'Active', capacity: 0, last_maintenance_date: '',
        location: '', model: '', plant_id: '', section_id: ''
    });
    const [error, setError] = useState('');

    const fetchData = async () => {
        try {
            const [machinesRes, plantsRes, sectionsRes] = await Promise.all([
                api.get('/machine.php?action=list'),
                api.get('/plant.php?action=list'),
                api.get('/section.php?action=list')
            ]);

            if (machinesRes.data.status === 'success') setMachines(machinesRes.data.data);
            if (plantsRes.data.status === 'success') setPlants(plantsRes.data.data);
            if (sectionsRes.data.status === 'success') setSections(sectionsRes.data.data);

        } catch (err) {
            setError('Failed to fetch data');
            console.error(err);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const handleShow = (machine = null) => {
        if (machine) {
            setCurrentMachine(machine);
            setFormData({
                name: machine.Name,
                status: machine.Status,
                capacity: machine.Capacity,
                last_maintenance_date: machine.LastMaintenanceDate ? machine.LastMaintenanceDate.split(' ')[0] : '',
                location: machine.Location,
                model: machine.Model,
                plant_id: machine.PlantID || '',
                section_id: machine.SectionID || ''
            });
        } else {
            setCurrentMachine(null);
            setFormData({
                name: '', status: 'Active', capacity: 0, last_maintenance_date: '',
                location: '', model: '', plant_id: '', section_id: ''
            });
        }
        setShowModal(true);
    };

    const handleClose = () => setShowModal(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        const data = new FormData();
        Object.keys(formData).forEach(key => data.append(key, formData[key]));

        try {
            if (currentMachine) {
                data.append('id', currentMachine.MachineID);
                await api.post('/machine.php?action=update', data);
            } else {
                await api.post('/machine.php?action=create', data);
            }
            fetchData();
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
            await api.post('/machine.php?action=delete', data);
            fetchData();
        }
    };

    const getPlantName = (id) => plants.find(p => p.PlantID == id)?.Name || id;
    const getSectionName = (id) => sections.find(s => s.SectionID == id)?.Name || id;

    return (
        <div>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h1>Machines</h1>
                <Button variant="primary" onClick={() => handleShow()}>Add Machine</Button>
            </div>
            {error && <Alert variant="danger">{error}</Alert>}
            <Table striped bordered hover responsive>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>Plant</th>
                        <th>Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {machines.map(machine => (
                        <tr key={machine.MachineID}>
                            <td>{machine.MachineID}</td>
                            <td>{machine.Name}</td>
                            <td>{machine.Model}</td>
                            <td>
                                <Badge bg={machine.Status === 'Active' ? 'success' : 'secondary'}>{machine.Status}</Badge>
                            </td>
                            <td>{getPlantName(machine.PlantID)}</td>
                            <td>{getSectionName(machine.SectionID)}</td>
                            <td>
                                <Button variant="outline-primary" size="sm" className="me-2" onClick={() => handleShow(machine)}>Edit</Button>
                                <Button variant="outline-danger" size="sm" onClick={() => handleDelete(machine.MachineID)}>Delete</Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </Table>

            <Modal show={showModal} onHide={handleClose} size="lg">
                <Modal.Header closeButton>
                    <Modal.Title>{currentMachine ? 'Edit Machine' : 'Add Machine'}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <Form onSubmit={handleSubmit}>
                        <div className="row">
                            <div className="col-md-6">
                                <Form.Group className="mb-3">
                                    <Form.Label>Name</Form.Label>
                                    <Form.Control type="text" value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} required />
                                </Form.Group>
                            </div>
                            <div className="col-md-6">
                                <Form.Group className="mb-3">
                                    <Form.Label>Model</Form.Label>
                                    <Form.Control type="text" value={formData.model} onChange={(e) => setFormData({...formData, model: e.target.value})} />
                                </Form.Group>
                            </div>
                        </div>

                        <div className="row">
                             <div className="col-md-6">
                                <Form.Group className="mb-3">
                                    <Form.Label>Plant</Form.Label>
                                    <Form.Select value={formData.plant_id} onChange={(e) => setFormData({...formData, plant_id: e.target.value})}>
                                        <option value="">Select Plant</option>
                                        {plants.map(p => <option key={p.PlantID} value={p.PlantID}>{p.Name}</option>)}
                                    </Form.Select>
                                </Form.Group>
                            </div>
                             <div className="col-md-6">
                                <Form.Group className="mb-3">
                                    <Form.Label>Section</Form.Label>
                                    <Form.Select value={formData.section_id} onChange={(e) => setFormData({...formData, section_id: e.target.value})}>
                                        <option value="">Select Section</option>
                                        {sections.map(s => <option key={s.SectionID} value={s.SectionID}>{s.Name}</option>)}
                                    </Form.Select>
                                </Form.Group>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-4">
                                <Form.Group className="mb-3">
                                    <Form.Label>Status</Form.Label>
                                    <Form.Select value={formData.status} onChange={(e) => setFormData({...formData, status: e.target.value})}>
                                        <option value="Active">Active</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Stopped">Stopped</option>
                                    </Form.Select>
                                </Form.Group>
                            </div>
                             <div className="col-md-4">
                                <Form.Group className="mb-3">
                                    <Form.Label>Capacity</Form.Label>
                                    <Form.Control type="number" step="0.01" value={formData.capacity} onChange={(e) => setFormData({...formData, capacity: e.target.value})} />
                                </Form.Group>
                            </div>
                             <div className="col-md-4">
                                <Form.Group className="mb-3">
                                    <Form.Label>Last Maint.</Form.Label>
                                    <Form.Control type="date" value={formData.last_maintenance_date} onChange={(e) => setFormData({...formData, last_maintenance_date: e.target.value})} />
                                </Form.Group>
                            </div>
                        </div>

                        <Form.Group className="mb-3">
                            <Form.Label>Location</Form.Label>
                            <Form.Control type="text" value={formData.location} onChange={(e) => setFormData({...formData, location: e.target.value})} />
                        </Form.Group>

                        <Button variant="primary" type="submit">Save</Button>
                    </Form>
                </Modal.Body>
            </Modal>
        </div>
    );
};

export default Machines;