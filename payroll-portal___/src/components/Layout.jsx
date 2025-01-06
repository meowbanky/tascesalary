// src/components/Layout.jsx
import { useState } from 'react';
import { UserButton, useUser } from '@clerk/clerk-react';
import { Outlet } from 'react-router-dom';
import { Sun, Moon, Bell } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";

const NotificationItem = ({ title, description, time, isRead }) => (
    <div className={`p-4 ${!isRead ? 'bg-primary/5' : ''} hover:bg-secondary`}>
        <div className="flex justify-between items-start mb-1">
            <h4 className="text-sm font-medium">{title}</h4>
            <span className="text-xs text-muted-foreground">{time}</span>
        </div>
        <p className="text-sm text-muted-foreground">{description}</p>
    </div>
);

export default function Layout({ darkMode, setDarkMode }) {
    const { user } = useUser();
    const [notifications] = useState([
        {
            id: 1,
            title: "Payslip Generated",
            description: "Your January 2024 payslip is now available",
            time: "2 hours ago",
            isRead: false
        },
        {
            id: 2,
            title: "Tax Document Update",
            description: "New W-2 form available for download",
            time: "1 day ago",
            isRead: false
        },
        {
            id: 3,
            title: "Benefits Update",
            description: "Review your updated benefits package",
            time: "3 days ago",
            isRead: true
        }
    ]);

    return (
        <div className="min-h-screen bg-background">
            <nav className="border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16 items-center">
                        <div className="flex-shrink-0">
                            <h1 className="text-xl font-bold">
                                Payroll Portal
                            </h1>
                        </div>

                        <div className="flex items-center gap-4">
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={() => setDarkMode(!darkMode)}
                                className="h-9 w-9"
                            >
                                {darkMode ? (
                                    <Sun className="h-4 w-4" />
                                ) : (
                                    <Moon className="h-4 w-4" />
                                )}
                            </Button>

                            <Popover>
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="h-9 w-9 relative"
                                    >
                                        <Bell className="h-4 w-4" />
                                        {notifications.some(n => !n.isRead) && (
                                            <span className="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full" />
                                        )}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-80 p-0" align="end">
                                    <div className="flex items-center justify-between px-4 py-2 border-b">
                                        <h3 className="font-semibold">Notifications</h3>
                                        <Button variant="ghost" size="sm">
                                            Mark all as read
                                        </Button>
                                    </div>
                                    <ScrollArea className="h-[300px]">
                                        {notifications.map((notification) => (
                                            <NotificationItem
                                                key={notification.id}
                                                {...notification}
                                            />
                                        ))}
                                    </ScrollArea>
                                    <div className="p-4 border-t">
                                        <Button variant="outline" className="w-full">
                                            View all notifications
                                        </Button>
                                    </div>
                                </PopoverContent>
                            </Popover>

                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        className="relative h-9 w-9 rounded-full"
                                    >
                                        <Avatar className="h-9 w-9">
                                            <AvatarImage src={user?.imageUrl} alt={user?.fullName} />
                                            <AvatarFallback>
                                                {user?.firstName?.[0]}
                                                {user?.lastName?.[0]}
                                            </AvatarFallback>
                                        </Avatar>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent className="w-56" align="end">
                                    <DropdownMenuLabel className="font-normal">
                                        <div className="flex flex-col space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {user?.fullName}
                                            </p>
                                            <p className="text-xs leading-none text-muted-foreground">
                                                {user?.primaryEmailAddress?.emailAddress}
                                            </p>
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem>Profile</DropdownMenuItem>
                                    <DropdownMenuItem>Settings</DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem className="text-red-600">
                                        Sign out
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </nav>
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <Outlet />
            </main>
        </div>
    );
}