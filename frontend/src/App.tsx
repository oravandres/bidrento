import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import PropertyTree from './components/PropertyTree';
import AddProperty from './components/AddProperty';

const App: React.FC = () => {
    return (
        <Router>
            <div className="container">
                <Routes>
                    <Route path="/" element={<PropertyTree />} />
                    <Route path="/add-property" element={<AddProperty />} />
                </Routes>
            </div>
        </Router>
    );
};

export default App;
