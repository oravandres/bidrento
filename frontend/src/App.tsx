import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import PropertyTree from './components/PropertyTree';

const App: React.FC = () => {
    return (
        <Router>
            <div className="container">
                <Routes>
                    <Route path="/" element={<PropertyTree />} />
                </Routes>
            </div>
        </Router>
    );
};

export default App;
