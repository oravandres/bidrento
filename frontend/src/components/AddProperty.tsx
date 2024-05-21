import React from 'react';
import { addProperty } from '../services/api';
import AddPropertyForm from './AddPropertyForm';

const AddProperty: React.FC = () => {
    const handleAddProperty = async (data: { name: string; type: string; parent_id?: number }) => {
        await addProperty(data);
    };

    return (
        <div>
            <h1>Add Property</h1>
            <AddPropertyForm onSubmit={handleAddProperty} />
        </div>
    );
};

export default AddProperty;
