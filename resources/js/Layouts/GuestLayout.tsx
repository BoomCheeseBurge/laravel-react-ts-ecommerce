import ApplicationLogo from '@/Components/Application/ApplicationLogo';
import NavBar from '@/Components/Application/Navigation/NavBar';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <NavBar />
            
            <div className='flex flex-col items-center pt-6 sm:justify-center sm:pt-20'>
                <div>
                    <Link href="/">
                        <ApplicationLogo className="w-20 h-20 text-gray-500 fill-current" />
                    </Link>
                </div>

                <div className="w-full overflow-hidden px-6 py-4 mt-6 bg-white shadow-md dark:bg-gray-800 sm:max-w-md sm:rounded-lg">
                    {children}
                </div>
            </div>
        </div>
    );
}
