import ApplicationLogo from '@/Components/Application/ApplicationLogo';
import NavBar from '@/Components/Application/Navigation/NavBar';
import Dropdown from '@/Components/Core/Dropdown';
import NavLink from '@/Components/Core/NavLink';
import ResponsiveNavLink from '@/Components/Core/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {

    const pageProps = usePage().props;
    const user = pageProps.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <NavBar />

            {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <div className="mx-auto max-w-7xl px-4 py-6 lg:px-8 sm:px-6">
                        {header}
                    </div>
                </header>
            )}

            {/* Display error message if any */}
            {pageProps.error && (
                <div className="container mx-auto px-8 mt-8">
                    <div className="alert alert-error">
                        {pageProps.error}
                    </div>
                </div>
            )}

            <main>{children}</main>
        </div>
    );
}
