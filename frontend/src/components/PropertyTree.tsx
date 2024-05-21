import React, { useEffect, useState, useCallback } from 'react';
import { getProperties } from '../services/api';
import { Button, Spinner, Alert } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlus } from '@fortawesome/free-solid-svg-icons';
import AddPropertyModal from './AddPropertyModal';
import DeleteModal from './DeleteModal';
import PropertyTreeNode from './PropertyTreeNode';

interface Property {
    id: number;
    name: string;
    type: string;
    children: Property[];
}

const PropertyTree: React.FC = () => {
    const [properties, setProperties] = useState<Property[]>([]);
    const [loading, setLoading] = useState(false);
    const [fetchError, setFetchError] = useState<string | null>(null);
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [parentPropertyId, setParentPropertyId] = useState<number | null>(null);
    const [propertyToDelete, setPropertyToDelete] = useState<number | null>(null);

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
    };

    const handleShowDeleteModal = (id: number) => {
        setPropertyToDelete(id);
        setShowDeleteModal(true);
    };

    const handleCloseDeleteModal = () => {
        setShowDeleteModal(false);
        setPropertyToDelete(null);
    };

    const handlePropertyAdded = () => {
        fetchProperties();
    };

    const handlePropertyDeleted = (id: number) => {
        setProperties((prevProperties) => removePropertyById(prevProperties, id));
    };

    const removePropertyById = (properties: Property[], id: number): Property[] => {
        return properties
            .filter((property) => property.id !== id)
            .map((property) => ({
                ...property,
                children: removePropertyById(property.children, id),
            }));
    };

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
                    {properties.map((property) => (
                        <PropertyTreeNode
                            key={property.id}
                            property={property}
                            onShowModal={handleShowModal}
                            onShowDeleteModal={handleShowDeleteModal}
                        />
                    ))}
                </ul>
            </>

            <AddPropertyModal
                show={showModal}
                handleClose={handleCloseModal}
                parentId={parentPropertyId}
                onPropertyAdded={handlePropertyAdded}
            />

            <DeleteModal
                show={showDeleteModal}
                handleClose={handleCloseDeleteModal}
                propertyId={propertyToDelete}
                onPropertyDeleted={handlePropertyDeleted}
            />
        </div>
    );
};

export default PropertyTree;
