import React from 'react';
import { Button } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlus, faTrash, faHome, faCar } from '@fortawesome/free-solid-svg-icons';

interface Property {
    id: number;
    name: string;
    type: string;
    children: Property[];
}

interface PropertyTreeNodeProps {
    property: Property;
    onShowModal: (parentId: number | null) => void;
    onShowDeleteModal: (id: number) => void;
}

const PropertyTreeNode: React.FC<PropertyTreeNodeProps> = ({ property, onShowModal, onShowDeleteModal }) => {
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

    return (
        <li>
            {getIconForType(property.type)} {property.name}
            <Button variant="link" size="sm" onClick={() => onShowModal(property.id)}>
                <FontAwesomeIcon icon={faPlus} />
            </Button>
            <Button variant="link" size="sm" onClick={() => onShowDeleteModal(property.id)}>
                <FontAwesomeIcon icon={faTrash} />
            </Button>
            {property.children && property.children.length > 0 && (
                <ul>
                    {property.children.map((child) => (
                        <PropertyTreeNode
                            key={child.id}
                            property={child}
                            onShowModal={onShowModal}
                            onShowDeleteModal={onShowDeleteModal}
                        />
                    ))}
                </ul>
            )}
        </li>
    );
};

export default PropertyTreeNode;
