import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import React from 'react';
import ReactDOM from 'react-dom';
import './index.css'
import App from './App.jsx'
import { ClerkProvider } from '@clerk/clerk-react';

const CLERK_PUBLISHABLE_KEY = 'pk_test_c2luZ3VsYXItaGFyZS05Ni5jbGVyay5hY2NvdW50cy5kZXYk';

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>

            <App />
       
    </React.StrictMode>
);