import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import VendorDetails from './Partials/VendorDetails';

export default function Edit({
    mustVerifyEmail,
    status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Profile
                </h2>
            }
        >
            <Head title="Profile" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl grid grid-cols-1 gap-4 p-4 md:grid-cols-3">
                    {/* Left Side */}
                    <div className="col-span-2 space-y-6">
                        <div className="p-4 bg-white shadow dark:bg-gray-800 sm:p-8 sm:rounded-lg">
                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                className="max-w-xl"
                            />
                        </div>

                        <div className="p-4 bg-white shadow dark:bg-gray-800 sm:p-8 sm:rounded-lg">
                            <UpdatePasswordForm className="max-w-xl" />
                        </div>

                        <div className="p-4 bg-white shadow dark:bg-gray-800 sm:p-8 sm:rounded-lg">
                            <DeleteUserForm className="max-w-xl" />
                        </div>
                    </div>

                    {/* Right Side */}
                    <div className="p-4 bg-white shadow dark:bg-gray-800 sm:p-8 sm:rounded-lg">
                        <VendorDetails />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
