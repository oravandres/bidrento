import React, { useState } from 'react';
import { addProperty } from '../services/api';

const AddProperty: React.FC = () => {
    const [name, setName] = useState('');
    const [parentId, setParentId] = useState<number | null>(null);
    const [type, setType] = useState('property');

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        await addProperty({ name, type, parent_id: parentId ?? undefined });
        setName('');
        setParentId(null);
        setType('property');
    };

    return (
        <div>
            <h1>Add Property</h1>
            <form onSubmit={handleSubmit}>
                <div>
                    <label>Name:</label>
                    <input
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                    />
                </div>
                <div>
                    <label>Parent ID (optional):</label>
                    <input
                        type="number"
                        value={parentId ?? ''}
                        onChange={(e) => setParentId(Number(e.target.value))}
                    />
                </div>
                <div>
                    <label>Type:</label>
                    <select value={type} onChange={(e) => setType(e.target.value)}>
                        <option value="property">Property</option>
                        <option value="parking_space">Parking Space</option>
                    </select>
                </div>
                <button type="submit">Add Property</button>
            </form>
        </div>
    );
};

export default AddProperty;
