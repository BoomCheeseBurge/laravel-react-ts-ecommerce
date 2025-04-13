import InputError from "@/Components/Core/InputError";
import InputLabel from "@/Components/Core/InputLabel";
import Modal from "@/Components/Core/Modal";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import SecondaryButton from "@/Components/Core/SecondaryButton";
import TextInput from "@/Components/Core/TextInput";
import { Textarea } from "@headlessui/react";
import { useForm, usePage } from "@inertiajs/react";
import React, { FormEventHandler, useState } from "react";

function VendorDetails({ className = '' }: { className?: string }) {
    
    // Show a modal window to ask confirmation on becoming a vendor
    const [showBecomeVendorConfirmation, setShowBecomeVendorConfirmation] = useState(false);
    const [successMessage, setSuccessMessage] = useState('');

    const user = usePage().props.auth.user;
    const token = usePage().props.csrf_token;

    const {
        data,
        setData,
        errors,
        post,
        processing,
        recentlySuccessful,
    } = useForm({
        store_name: user.vendor?.store_name || user.name.toLowerCase().replace(/\s+/g, '-'),
        store_address: user.vendor?.store_address ?? '',
    });

    // Store vendor store name in the form data field
    const onStoreNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {

        setData('store_name', 
            event.target.value.toLowerCase().replace(/\s+/g, '-') // Convert to slug
        );
    };

    // Submit become vendor form
    const becomeVendor: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('vendor.register'), {
            preserveScroll: true,
            onSuccess: () => {
                // Close the modal window
                closeModal();

                // Set success message
                // setSuccessMessage('You can now create and publish products');
                setSuccessMessage('Please await an email from us for further info.');
            },
            onError: (errors) => {},
        });
    };

    // Update vendor information
    const updateVendor: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('vendor.store'), {
            preserveScroll: true,
            onSuccess: () => {
                // Close the modal window
                closeModal();

                // Set success message
                setSuccessMessage('Your details were updated.')
            },
            onError: (errors) => {},
        })
    };

    // Close modal window
    const closeModal = () => {
        setShowBecomeVendorConfirmation(false);
    };

    return (
        <section className={className} >
            {/* Show success message */}
            {recentlySuccessful && (
                <div className="toast toast-end toast-top">
                    <div className="alert alert-success">
                        <span>{successMessage}</span>
                    </div>
                </div>
            )}

            {/* Vendor Status */}
            <header>
                <h2 className="flex justify-between mb-8 text-lg font-medium text-gray-900 dark:text-gray-100">
                    Vendor Details

                    {/* Display badge of either vendor status */}
                    {user.vendor?.status === 'pending' && (
                        <span className="badge badge-warning py-3 text-white">
                            {user.vendor.status_label}
                        </span>
                    )}
                    {user.vendor?.status === 'approved' && (
                        <span className="badge badge-success py-3 text-white">
                            {user.vendor.status_label}
                        </span>
                    )}
                    {user.vendor?.status === 'rejected' && (
                        <span className="badge badge-error py-3 text-white">
                            {user.vendor.status_label}
                        </span>
                    )}
                </h2>
            </header>

            {/* Display Vendor Details */}
            <div>
                {/* Show become a vendor button */}
                {!user.vendor && (
                    <PrimaryButton disabled={processing} onClick={e => setShowBecomeVendorConfirmation(true)} >
                        Become a Vendor
                    </PrimaryButton>
                )}

                {/* Show info that this user's vendor request is being processed */}
                {user.vendor?.status === 'pending' && (
                    <div>Your vendor request is being processed by us. Await an email from us!</div>
                )}

                {/* If user is an approved vendor */}
                {user.vendor?.status === "approved" && (
                    <>
                        <form onSubmit={updateVendor} className="mb-5" >
                            {/* Vendor Store Name Input */}
                            <div className="mb-4">
                                <InputLabel htmlFor="name" value="Store Name" />

                                <TextInput id="name" 
                                    className="w-full block mt-1"
                                    value={data.store_name}
                                    onChange={onStoreNameChange}
                                    required
                                    isFocused
                                    autoComplete="name"
                                />

                                <InputError className="mt-2" message={errors.store_name} />
                            </div>

                            {/* Vendor Store Address Input */}
                            <div className="mb-4">
                                <InputLabel htmlFor="address" value="Store Address" />

                                <Textarea id="address"
                                    className="textarea textarea-bordered w-full mt-1"
                                    value={data.store_address}
                                    onChange={e => setData('store_address', e.target.value)} 
                                    placeholder="Enter your store address"
                                ></Textarea>

                                <InputError className="mt-2" message={errors.store_address} />
                            </div>

                            {/* Save the form */}
                            <div className="flex items-center gap-4">
                                <PrimaryButton disabled={processing} >Update</PrimaryButton>
                            </div>
                        </form>

                        <hr />

                        {/* A user may be a vendor but have not connected to Stripe account yet */}
                        <form action={route('stripe.connect')} method='POST' className="my-8" >
                            {/* Include the CSRF token */}
                            <input type="hidden" name="_token" value={token} />

                            <div className="mb-3 text-lg font-semibold text-white" >Already connected to Stripe?</div>

                            {/* Show badge if the vendor user has already connected to Stripe */}
                            {user.stripe_account_active && (
                                <div className="w-52 px-4 py-2 font-semibold text-center text-white bg-emerald-400 rounded-lg shadow-md select-none">Connected to Stripe</div>
                            )}

                            {/* Let the vendor user connect to Stripe */}
                            {(!user.stripe_account_active) && (
                                <button type="submit" 
                                    className={"inset-shadow-rose-500/50 inset-shadow-sm px-4 py-2 font-semibold rounded-lg " + (user.vendor.status === 'approved' ? "text-white bg-red-600" : "text-slate-300 bg-red-800") } 
                                    disabled={user.vendor.status != 'approved'}>
                                    Connect to Stripe
                                </button>
                            )}
                        </form>
                    </>
                )}

                {user.vendor.status == "rejected" && (
                    <div className="mb-2">
                        <div className="bg-primary card w-full text-white">
                            <div className="card-body">
                                <h2 className="card-title">Uh Oh!</h2>
                                
                                <div>
                                    Your account is not eligible to connect to stripe. <br />
                                    Please contact our administrator.
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Become a Vendor Modal */}
            <Modal show={showBecomeVendorConfirmation} onClose={closeModal} maxWidth="md" >
                <form onSubmit={becomeVendor} className="flex flex-col justify-center items-center p-4">
                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Register to become a vendor of Larastore?
                    </h2>

                    <div className="flex justify-end mt-6">
                        <PrimaryButton disabled={processing} >
                            Confirm
                        </PrimaryButton>

                        <SecondaryButton onClick={closeModal} className="ms-3" >
                            Cancel
                        </SecondaryButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}

export default VendorDetails;