import React, { useState, useEffect } from 'react';
import { Modal, Button, Form, Alert } from 'react-bootstrap';
import { addProperty } from '../services/api';

interface AddPropertyModalProps {
    show: boolean;
    handleClose: () => void;
    parentId: number | null;
    onPropertyAdded: () => void;
}

const AddPropertyModal: React.FC<AddPropertyModalProps> = ({ show, handleClose, parentId, onPropertyAdded }) => {
    const [newPropertyName, setNewPropertyName] = useState('');
    const [newPropertyType, setNewPropertyType] = useState('property');
    const [addError, setAddError] = useState<string | null>(null);

    useEffect(() => {
        if (!show) {
            setNewPropertyName('');
            setNewPropertyType('property');
            setAddError(null);
        }
    }, [show]);

    const handleAddProperty = async () => {
        try {
            await addProperty({ name: newPropertyName, type: newPropertyType, parent_id: parentId ?? undefined });
            handleClose();
            onPropertyAdded();
        } catch (err: any) {
            setAddError(err.response.data.error);
        }
    };

    return (
        <Modal show={show} onHide={handleClose}>
            <Modal.Header closeButton>
                <Modal.Title>Add Property</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {addError && <Alert variant="danger">{addError}</Alert>}
                <Form>
                    <Form.Group controlId="propertyName">
                        <Form.Label>Property Name</Form.Label>
                        <Form.Control
                            type="text"
                            placeholder="Enter property name"
                            value={newPropertyName}
                            onChange={(e) => setNewPropertyName(e.target.value)}
                        />
                    </Form.Group>
                    <Form.Group controlId="propertyType">
                        <Form.Label>Property Type</Form.Label>
                        <Form.Control
                            as="select"
                            value={newPropertyType}
                            onChange={(e) => setNewPropertyType(e.target.value)}
                        >
                            <option value="property">Property</option>
                            <option value="parking_space">Parking Space</option>
                        </Form.Control>
                    </Form.Group>
                </Form>
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={handleClose}>
                    Close
                </Button>
                <Button variant="primary" onClick={handleAddProperty}>
                    Save Changes
                </Button>
            </Modal.Footer>
        </Modal>
    );
};

export default AddPropertyModal;
