import React, { useEffect, useState, useCallback } from 'react';
import { getProperties, addProperty, deleteProperty } from '../services/api';
import { Button, Modal, Form, Spinner, Alert } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlus, faTrash, faHome, faCar } from '@fortawesome/free-solid-svg-icons';

interface Property {
    id: number;
    name: string;
    type: string;
    children: Property[];
}

const PropertyTree: React.FC = () => {
    const [properties, setProperties] = useState<Property[]>([]);
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [newPropertyName, setNewPropertyName] = useState('');
    const [newPropertyType, setNewPropertyType] = useState('property');
    const [parentPropertyId, setParentPropertyId] = useState<number | null>(null);
    const [loading, setLoading] = useState(false);
    const [propertyToDelete, setPropertyToDelete] = useState<number | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [addError, setAddError] = useState<string | null>(null);
    const [fetchError, setFetchError] = useState<string | null>(null);

    const fetchProperties = useCallback(async () => {
        setLoading(true);
        try {
            const response = await getProperties();
            setProperties(response.data);
            setFetchError(null);
        } catch (err: any) {
            setFetchError(err.message);
        }
        setLoading(false);
    }, []);

    useEffect(() => {
        fetchProperties();
    }, [fetchProperties]);

    const handleShowModal = (parentId: number | null) => {
        setParentPropertyId(parentId);
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setNewPropertyName('');
        setNewPropertyType('property');
        setParentPropertyId(null);
        setAddError(null);
    };

    const handleAddProperty = async () => {
        setLoading(true);
        try {
            await addProperty({ name: newPropertyName, type: newPropertyType, parent_id: parentPropertyId ?? undefined });
            handleCloseModal();
            await fetchProperties();
        } catch (err: any) {
            setAddError(err.response.data.error);
        }
        setLoading(false);
    };

    const handleShowDeleteModal = (id: number) => {
        setPropertyToDelete(id);
        setShowDeleteModal(true);
    };

    const handleCloseDeleteModal = () => {
        setShowDeleteModal(false);
        setPropertyToDelete(null);
        setError(null);
    };

    const removePropertyById = (properties: Property[], id: number): Property[] => {
        return properties
            .filter((property) => property.id !== id)
            .map((property) => ({
                ...property,
                children: removePropertyById(property.children, id),
            }));
    };

    const handleDeleteProperty = async () => {
        if (propertyToDelete !== null) {
            setLoading(true);
            try {
                await deleteProperty(propertyToDelete);
                setProperties((prevProperties) => removePropertyById(prevProperties, propertyToDelete));
                handleCloseDeleteModal();
            } catch (err: any) {
                console.log(err);
                setError(err.response.data.error);
            }
            setLoading(false);
        }
    };

    const getIconForType = (type: string) => {
        switch (type) {
            case 'property':
                return <FontAwesomeIcon icon={faHome} />;
            case 'parking_space':
                return <FontAwesomeIcon icon={faCar} />;
            default:
                return null;
        }
    };

    const renderTree = (property: Property) => (
        <li key={property.id}>
            {getIconForType(property.type)} {property.name}
            <Button variant="link" size="sm" onClick={() => handleShowModal(property.id)}>
                <FontAwesomeIcon icon={faPlus} />
            </Button>
            <Button variant="link" size="sm" onClick={() => handleShowDeleteModal(property.id)}>
                <FontAwesomeIcon icon={faTrash} />
            </Button>
            {property.children && property.children.length > 0 && (
                <ul>
                    {property.children.map((child) => renderTree(child))}
                </ul>
            )}
        </li>
    );

    return (
        <div className="container">
            <h1>Property Tree</h1>
            {loading && <Spinner animation="border" style={{ position: 'fixed', top: '10px', right: '10px' }} />}
            {fetchError && <Alert variant="danger">{fetchError}</Alert>}
            <>
                <Button onClick={() => handleShowModal(null)}>
                    <FontAwesomeIcon icon={faPlus} /> Add Property
                </Button>
                <ul className="property-tree">
                    {properties.map((property) => renderTree(property))}
                </ul>
            </>

            <Modal show={showModal} onHide={handleCloseModal}>
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
                    <Button variant="secondary" onClick={handleCloseModal}>
                        Close
                    </Button>
                    <Button variant="primary" onClick={handleAddProperty}>
                        Save Changes
                    </Button>
                </Modal.Footer>
            </Modal>

            <Modal show={showDeleteModal} onHide={handleCloseDeleteModal}>
                <Modal.Header closeButton>
                    <Modal.Title>Delete Property</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    {error && <Alert variant="danger">{error}</Alert>}
                    Are you sure you want to delete this property?
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={handleCloseDeleteModal}>
                        Cancel
                    </Button>
                    <Button variant="danger" onClick={handleDeleteProperty}>
                        Delete
                    </Button>
                </Modal.Footer>
            </Modal>
        </div>
    );
};

export default PropertyTree;
