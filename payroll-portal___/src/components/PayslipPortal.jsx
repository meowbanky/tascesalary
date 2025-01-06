import React, { useState } from 'react';
import { User, LogOut, FileText, Calendar, Bell, Settings, Download, Printer, Mail, Building, Phone, Badge, CreditCard } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const PayslipPortal = () => {
    const [darkMode, setDarkMode] = useState(false);
    const [selectedMonth, setSelectedMonth] = useState('');
    const [activeTab, setActiveTab] = useState('payslip');
    const [showNotifications, setShowNotifications] = useState(false);

    // Mock data - replace with API calls
    const [payslip, setPayslip] = useState({
        basicSalary: 5000,
        allowances: 800,
        deductions: 500,
        netPay: 5300,
        paymentDate: '2024-03-25',
        bankAccount: '**** **** **** 1234'
    });

    const [profile] = useState({
        name: 'John Doe',
        employeeId: 'EMP001',
        department: 'Engineering',
        position: 'Senior Developer',
        email: 'john.doe@company.com',
        phone: '+1 234 567 8900',
        joinDate: '2022-01-15'
    });

    const [notifications] = useState([
        { id: 1, message: 'Your March payslip is ready', date: '2024-03-25', unread: true },
        { id: 2, message: 'Tax documents updated', date: '2024-03-20', unread: false }
    ]);

    const months = [
        'January', 'February', 'March', 'April',
        'May', 'June', 'July', 'August',
        'September', 'October', 'November', 'December'
    ];

    const handleMonthSelect = (month) => {
        setSelectedMonth(month);
        // fetchPayslip(month);
    };

    return (
        <div className={`min-h-screen ${darkMode ? 'dark bg-gray-900' : 'bg-gray-50'}`}>
            {/* Navigation Bar */}
            <nav className="bg-white dark:bg-gray-800 shadow-lg">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <h1 className="text-xl font-bold text-gray-800 dark:text-white">
                                Employee Payslip Portal
                            </h1>
                        </div>
                        <div className="flex items-center space-x-4">
                            <DropdownMenu>
                                <DropdownMenuTrigger className="relative">
                                    <Bell className="h-5 w-5 text-gray-600 dark:text-gray-300" />
                                    {notifications.some(n => n.unread) && (
                                        <span className="absolute -top-1 -right-1 h-2 w-2 bg-red-500 rounded-full"></span>
                                    )}
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    {notifications.map(notification => (
                                        <DropdownMenuItem key={notification.id} className="flex flex-col items-start">
                      <span className={`text-sm ${notification.unread ? 'font-bold' : ''}`}>
                        {notification.message}
                      </span>
                                            <span className="text-xs text-gray-500">{notification.date}</span>
                                        </DropdownMenuItem>
                                    ))}
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <button
                                onClick={() => setDarkMode(!darkMode)}
                                className="p-2 rounded-lg bg-gray-200 dark:bg-gray-700"
                            >
                                {darkMode ? '🌞' : '🌙'}
                            </button>

                            <DropdownMenu>
                                <DropdownMenuTrigger className="flex items-center space-x-2 text-gray-700 dark:text-gray-200">
                                    <User size={20} />
                                    <span>{profile.name}</span>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem>
                                        <User className="mr-2 h-4 w-4" />
                                        Profile
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <Settings className="mr-2 h-4 w-4" />
                                        Settings
                                    </DropdownMenuItem>
                                    <DropdownMenuItem className="text-red-600">
                                        <LogOut className="mr-2 h-4 w-4" />
                                        Logout
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <Tabs defaultValue="payslip" className="space-y-4">
                    <TabsList className="grid w-full grid-cols-2">
                        <TabsTrigger value="payslip">Payslip</TabsTrigger>
                        <TabsTrigger value="profile">Profile</TabsTrigger>
                    </TabsList>

                    <TabsContent value="payslip">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Month Selection Card */}
                            <Card className="dark:bg-gray-800">
                                <CardHeader>
                                    <CardTitle className="text-gray-800 dark:text-white flex items-center gap-2">
                                        <Calendar className="h-5 w-5" />
                                        Select Month
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-3 gap-2">
                                        {months.map((month) => (
                                            <button
                                                key={month}
                                                onClick={() => handleMonthSelect(month)}
                                                className={`p-2 rounded-lg text-sm ${
                                                    selectedMonth === month
                                                        ? 'bg-blue-500 text-white'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                                                }`}
                                            >
                                                {month}
                                            </button>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Payslip Details Card */}
                            <Card className="dark:bg-gray-800">
                                <CardHeader>
                                    <CardTitle className="text-gray-800 dark:text-white flex items-center gap-2">
                                        <FileText className="h-5 w-5" />
                                        Payslip Details
                                    </CardTitle>
                                    {selectedMonth && (
                                        <CardDescription>
                                            Payment Date: {payslip.paymentDate}
                                        </CardDescription>
                                    )}
                                </CardHeader>
                                <CardContent>
                                    {selectedMonth ? (
                                        <div className="space-y-4">
                                            <div className="grid grid-cols-2 gap-4">
                                                <div className="space-y-2">
                                                    <p className="text-sm text-gray-500 dark:text-gray-400">Basic Salary</p>
                                                    <p className="text-lg font-semibold text-gray-800 dark:text-white">
                                                        ${payslip.basicSalary}
                                                    </p>
                                                </div>
                                                <div className="space-y-2">
                                                    <p className="text-sm text-gray-500 dark:text-gray-400">Allowances</p>
                                                    <p className="text-lg font-semibold text-green-600 dark:text-green-400">
                                                        +${payslip.allowances}
                                                    </p>
                                                </div>
                                                <div className="space-y-2">
                                                    <p className="text-sm text-gray-500 dark:text-gray-400">Deductions</p>
                                                    <p className="text-lg font-semibold text-red-600 dark:text-red-400">
                                                        -${payslip.deductions}
                                                    </p>
                                                </div>
                                                <div className="space-y-2">
                                                    <p className="text-sm text-gray-500 dark:text-gray-400">Net Pay</p>
                                                    <p className="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                                        ${payslip.netPay}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                                                <p className="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                                    Payment Account: {payslip.bankAccount}
                                                </p>
                                                <div className="flex space-x-2">
                                                    <button className="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                                                        <Download className="h-4 w-4" />
                                                        Download PDF
                                                    </button>
                                                    <button className="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                                                        <Printer className="h-4 w-4" />
                                                        Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <Alert>
                                            <AlertDescription>
                                                Please select a month to view payslip details
                                            </AlertDescription>
                                        </Alert>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="profile">
                        <Card className="dark:bg-gray-800">
                            <CardHeader>
                                <CardTitle className="text-gray-800 dark:text-white">Employee Profile</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-2">
                                            <Badge className="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">Employee ID</p>
                                                <p className="text-gray-800 dark:text-white">{profile.employeeId}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Building className="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">Department</p>
                                                <p className="text-gray-800 dark:text-white">{profile.department}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <CreditCard className="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">Position</p>
                                                <p className="text-gray-800 dark:text-white">{profile.position}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-2">
                                            <Mail className="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                                <p className="text-gray-800 dark:text-white">{profile.email}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Phone className="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                                                <p className="text-gray-800 dark:text-white">{profile.phone}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-5 w-5 text-gray-500" />
                                            <div>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">Join Date</p>
                                                <p className="text-gray-800 dark:text-white">{profile.joinDate}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </main>
        </div>
    );
};

export default PayslipPortal;