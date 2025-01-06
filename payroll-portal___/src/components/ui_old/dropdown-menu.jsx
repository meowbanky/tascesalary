import * as React from "react"

const DropdownMenu = ({ children }) => (
    <div className="relative inline-block text-left">
        {children}
    </div>
)

const DropdownMenuTrigger = React.forwardRef(({ className, ...props }, ref) => (
    <button
        ref={ref}
        className={`inline-flex items-center justify-center ${className}`}
        {...props}
    />
))
DropdownMenuTrigger.displayName = "DropdownMenuTrigger"

const DropdownMenuContent = React.forwardRef(({ className, ...props }, ref) => (
    <div
        ref={ref}
        className={`absolute right-0 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ${className}`}
        {...props}
    />
))
DropdownMenuContent.displayName = "DropdownMenuContent"

const DropdownMenuItem = React.forwardRef(({ className, ...props }, ref) => (
    <button
        ref={ref}
        className={`block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 ${className}`}
        {...props}
    />
))
DropdownMenuItem.displayName = "DropdownMenuItem"

export { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem }