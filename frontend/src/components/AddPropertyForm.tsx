import React, { useState } from 'react';

interface AddPropertyFormProps {
    onSubmit: (data: { name: string; type: string; parent_id?: number }) => void;
}

const AddPropertyForm: React.FC<AddPropertyFormProps> = ({ onSubmit }) => {
    const [name, setName] = useState('');
    const [parentId, setParentId] = useState<number | null>(null);
    const [type, setType] = useState('property');

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => setName(e.target.value);
    const handleParentIdChange = (e: React.ChangeEvent<HTMLInputElement>) => setParentId(Number(e.target.value) || null);
    const handleTypeChange = (e: React.ChangeEvent<HTMLSelectElement>) => setType(e.target.value);

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        onSubmit({ name, type, parent_id: parentId ?? undefined });
        setName('');
        setParentId(null);
        setType('property');
    };

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <label>Name:</label>
                <input type="text" value={name} onChange={handleNameChange} />
            </div>
            <div>
                <label>Parent ID (optional):</label>
                <input type="number" value={parentId ?? ''} onChange={handleParentIdChange} />
            </div>
            <div>
                <label>Type:</label>
                <select value={type} onChange={handleTypeChange}>
                    <option value="property">Property</option>
                    <option value="parking_space">Parking Space</option>
                </select>
            </div>
            <button type="submit">Add Property</button>
        </form>
    );
};

export default AddPropertyForm;
