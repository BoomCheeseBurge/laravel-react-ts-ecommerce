import { Link, useForm, usePage } from "@inertiajs/react";
import Dropdown from "../Cart/Dropdown";
import { FormEventHandler, useState } from "react";
import { MagnifyingGlassIcon } from "@heroicons/react/24/outline";

function NavBar() {

    const { auth, departments, departmentParam, keyword } = usePage().props;
    const { user, is_admin_or_vendor } = auth;
    const [activeDept, setActiveDept] = useState<string>(departmentParam);

    const searchForm = useForm<{ keyword: string }>({
        keyword: keyword || '',
    });
    const {url} = usePage();

    /**
     * There's no special Inertia React method that perfectly mirrors the behavior of Inertia::location() 
     * because Inertia::location() triggers a full page reload via a server-side redirect, 
     * and that's fundamentally different from Inertia's client-side transitions.
     */
    const handleVisitDashboard = () => {
        window.location.href = route('filament.admin.pages.dashboard');
    };

    const onSearchProduct: FormEventHandler = (e) => {
        e.preventDefault();

        /**
         * Query String: Inertia.js automatically appends the form data to the URL as a query string.
         *
         * For example, if the current URL is /products and the keyword is "shoes," the request URL will become /products?keyword=shoes.
         *
         * Backend Retrieval: On the backend, the keyword value can be accessed from the query string using the request() helper.
         */
        searchForm.get(url, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <>
            {/* Main Navbar */}
            <div className="bg-base-100 navbar py-4">
                <div className="flex-1 ml-5">
                    <Link href="/" className="btn btn-ghost text-xl">LaraStore</Link>
                </div>
                
                <div className="flex-none gap-4 mr-10">
                    {/* ------------------------------ SEARCH-BAR FOR PRODUCTS ------------------------------ */}
                    <form onSubmit={onSearchProduct} className="join flex-1 me-10">
                        <div className="flex-1">
                            <input type="text" value={searchForm.data.keyword} onChange={(e) => searchForm.setData('keyword', e.target.value)} 
                                placeholder="Search by keyword..."
                                className="input input-bordered join-item w-full" 
                            />
                        </div>

                        <div className="indicator">
                            <button className="btn join-item">
                                <MagnifyingGlassIcon className="size-5" />
                                <span className="text-sm" >Search</span>
                            </button>
                        </div>
                    </form>

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

            {/* Sub-Navbar to display Products by Department */}
            <div className="bg-base-100 min-h-4 navbar border-t">
                <div className="navbar-center hidden lg:flex">
                    <ul className="menu menu-horizontal z-20 px-1 py-0">
                        {departments.map((department) => (
                            <li key={department.id} className={activeDept === department.slug ? 'border-b-2 border-b-orange-500' : ''} >
                                <Link href={route('product.byDepartment', department.slug)} onClick={() => setActiveDept(department.name)}>
                                    {department.name}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </>
    );
}

export default NavBar;