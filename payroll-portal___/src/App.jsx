import { useState } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ClerkProvider } from '@clerk/clerk-react';
import Layout from './components/Layout';
// import Dashboard from './pages/Dashboard';
// import PayslipView from './pages/PayslipView';

const CLERK_PUBLISHABLE_KEY = 'pk_test_c2luZ3VsYXItaGFyZS05Ni5jbGVyay5hY2NvdW50cy5kZXYk';

function App() {
    const [darkMode, setDarkMode] = useState(false);

    return (
        <ClerkProvider publishableKey={CLERK_PUBLISHABLE_KEY}>
            <BrowserRouter>
                <Routes>
                    <Route path="/" element={<Layout darkMode={darkMode} setDarkMode={setDarkMode} />}>
                        <Route index element={<Navigate to="/dashboard" replace />} />
                        {/*<Route path="dashboard" element={<Dashboard />} />*/}
                        {/*<Route path="payslip/:id" element={<PayslipView />} />*/}
                    </Route>
                </Routes>
            </BrowserRouter>
        </ClerkProvider>
    );
}

export default App;