import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import { route } from '@/utils/route';

interface VerifyEmailProps {
    status: string | null;
}

export default function VerifyEmail({ status }: VerifyEmailProps) {
    const verificationLinkSent = status === 'verification-link-sent';
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Email verification" />

            <p className="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Verify your email using the link we sent. You can request another link below.
            </p>

            {verificationLinkSent && (
                <p className="mb-4 text-sm font-medium text-green-600">
                    A new verification link has been sent to your email address.
                </p>
            )}

            <form onSubmit={submit} className="flex items-center justify-between">
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    Resend verification email
                </button>
                <Link href={route('logout')} method="post" as="button" className="text-sm text-gray-600 underline dark:text-gray-300">
                    Log out
                </Link>
            </form>
        </GuestLayout>
    );
}
