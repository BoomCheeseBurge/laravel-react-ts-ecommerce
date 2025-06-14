import InputError from '@/Components/Core/InputError';
import InputLabel from '@/Components/Core/InputLabel';
import PrimaryButton from '@/Components/Core/PrimaryButton';
import TextInput from '@/Components/Core/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { TextInputRef } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    // Check if the original and confirmed passwords are the same
    const [confirmPassword, setConfirmPassword] = useState(true);

    // Reference the original password input
    const passwordRef = useRef<TextInputRef>(null);

    const passwordConfirmation = (val: string) => {

        const passVal = passwordRef.current?.getValue();
        /**
         * Check if the password confirmation is not an empty string and not equal to the original password
         * 
         * Note: updater function is used to compare the latest value directly without waiting for a re-render
         */
        if (val != '') {
            if (passVal != val) {
                setConfirmPassword((p) => p = false);
            } else if (passVal === val) {
                setConfirmPassword((p) => p = true);
            }
        } else {
            setConfirmPassword((p) => p = true);
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="w-full block mt-1"
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="w-full block mt-1"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="w-full block mt-1"
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        ref={passwordRef}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="w-full block mt-1"
                        autoComplete="new-password"
                        onChange={(e) => {
                                setData('password_confirmation', e.target.value);
                                passwordConfirmation(e.target.value);
                            }
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                    {!confirmPassword && (
                        <InputError
                            message={'Passwords do not match'}
                            className="mt-2"
                        />
                    )}
                </div>

                <div className="flex justify-end items-center mt-4">
                    <Link
                        href={route('login')}
                        className="link"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
