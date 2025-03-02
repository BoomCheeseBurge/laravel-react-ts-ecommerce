import NavBar from '@/Components/Application/Navigation/NavBar';
import { usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useEffect, useState } from 'react';

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {

    const pageProps = usePage().props;
    const user = pageProps.auth.user;

    // Store multiple success messages
    const [successMessages, setSuccessMessages] = useState<any[]>([]);

    // Set session timeout for each success message
    // const timeoutRefs = useRef<{ [key: number]: ReturnType<typeof setTimeout>}>({});

    // Check if the session message changed
    useEffect(() => {
        // Check whether the success message is not empty
        if(pageProps.success.message) {
            const newMessage = {
                ...pageProps.success, // Get the message and time
                id: pageProps.success.time, // Time in milliseconds set as ID
            };

            // Add new message to the state (in-front of the previous messages)
            setSuccessMessages((prevMessages) => [newMessage, ...prevMessages]);

            // Set the timeout for the new message and assign the timeout ID
            const timeoutId = setTimeout(() => {

                // Use a functional update to ensure the latest state is used
                setSuccessMessages((prevMessages) => 
                    
                    // Filter out that new message object
                    prevMessages.filter((msg) => msg.id !== newMessage.id)
                );

                // Clear timeout object after execution from current property of the ref hook based on the
                // delete timeoutRefs.current[newMessage.id];
            }, 5000);

            // Store the timeout ID on the timeout ref with the key as the new message ID
            // timeoutRefs.current[newMessage.id] = timeoutId;

            // Cleanup to prevent memory leak (if any) when the setTimeout is defined
            return () => {
                clearTimeout(timeoutId);
            }
        }

        // No cleanup if there is no message.
        return undefined; // or return () => {};
    }, [pageProps.success]);
    

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

            {/* Display success message if any */}
            {successMessages.length > 0 && (
                
                <div className="toast toast-end toast-top z-[1000] mt-16">
                    {/* Display all the messages inside the state  */}
                    {successMessages.map((msg) => (
                        <div key={msg.id} className="alert alert-success">
                            <span>{msg.message}</span>
                        </div>
                    ))}
                </div>
            )}

            <main>{children}</main>
        </div>
    );
}
