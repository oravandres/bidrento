import React, { useState, useEffect } from 'react';
import { Modal, Button, Alert } from 'react-bootstrap';
import { deleteProperty } from '../services/api';

interface DeleteModalProps {
    show: boolean;
    handleClose: () => void;
    propertyId: number | null;
    onPropertyDeleted: (id: number) => void;
}

const DeleteModal: React.FC<DeleteModalProps> = ({ show, handleClose, propertyId, onPropertyDeleted }) => {
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!show) {
            setError(null);
        }
    }, [show]);

    const handleDeleteProperty = async () => {
        if (propertyId !== null) {
            try {
                await deleteProperty(propertyId);
                onPropertyDeleted(propertyId);
                handleClose();
            } catch (err: any) {
                setError(err.response.data.error);
            }
        }
    };

    return (
        <Modal show={show} onHide={handleClose}>
            <Modal.Header closeButton>
                <Modal.Title>Delete Property</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {error && <Alert variant="danger">{error}</Alert>}
                Are you sure you want to delete this property?
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={handleClose}>
                    Cancel
                </Button>
                <Button variant="danger" onClick={handleDeleteProperty}>
                    Delete
                </Button>
            </Modal.Footer>
        </Modal>
    );
};

export default DeleteModal;
