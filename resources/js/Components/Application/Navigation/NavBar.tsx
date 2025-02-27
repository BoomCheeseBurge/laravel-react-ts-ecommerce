import { Link, router, usePage } from "@inertiajs/react";
import Dropdown from "../Cart/Dropdown";

function NavBar() {

    const {auth, totalPrice, totalQuantity} = usePage().props;
    const {user, is_admin_or_vendor} = auth;

    /**
     * There's no special Inertia React method that perfectly mirrors the behavior of Inertia::location() 
     * because Inertia::location() triggers a full page reload via a server-side redirect, 
     * and that's fundamentally different from Inertia's client-side transitions.
     */
    const handleVisitDashboard = () => {
        window.location.href = route('filament.admin.pages.dashboard');
    };

    return (
        <div className="bg-base-100 navbar py-4">
            <div className="flex-1 ml-5">
                <Link href="/" className="btn btn-ghost text-xl">LaraStore</Link>
            </div>
            
            <div className="flex-none gap-4 mr-10">
                {/* ------------------------------ CART DROPDOWN ------------------------------ */}
                <Dropdown />

                {user && (
                    <>
                        {/* ------------------------------ PROFILE DROPDOWN ------------------------------ */}
                        <div className="dropdown dropdown-end">
                            <div tabIndex={0} role="button" className="avatar btn btn-circle btn-ghost">
                                <div className="w-10 rounded-full">
                                <img
                                    alt="Tailwind CSS Navbar component"
                                    src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                                </div>
                            </div>
                            <ul
                                tabIndex={0}
                                className="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-48 p-2 shadow gap-1">
                                {is_admin_or_vendor && (
                                    <li>
                                        <button type="button" onClick={handleVisitDashboard} className="justify-between">
                                            Dashboard
                                        </button>
                                    </li>
                                )}
                                <li>
                                    <Link href={route('profile.edit')} className="justify-between">
                                        Profile
                                    </Link>
                                </li>
                                <li>
                                    <Link href={route('logout')} method={"post"} as="button">Logout</Link>
                                </li>
                            </ul>
                        </div>
                    </>
                )}
                {!user && (
                    <div className="flex gap-6">
                        <Link href={route('login')} className="btn">Login</Link>
                        <Link href={route('register')} className="btn btn-primary">Register</Link>
                    </div>
                )}
            </div>
        </div>
    );
}

export default NavBar;