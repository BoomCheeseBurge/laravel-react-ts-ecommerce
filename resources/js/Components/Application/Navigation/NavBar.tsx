import { Link, useForm, usePage } from "@inertiajs/react";
import Dropdown from "../Cart/Dropdown";
import { FormEventHandler, useEffect, useRef, useState } from "react";
import { Bars3Icon, MagnifyingGlassIcon, XMarkIcon } from "@heroicons/react/24/outline";

function NavBar() {

    const { auth, departments, departmentParam, keyword } = usePage().props;
    const { user, is_admin_or_vendor } = auth;
    const [activeDept, setActiveDept] = useState<string>(departmentParam);
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false);

    const searchForm = useForm<{ keyword: string }>({
        keyword: keyword || '',
    });
    const {url} = usePage();

    // Reference to the hamburger menu
    const menuRef = useRef<HTMLDivElement | null>(null);
    const buttonRef = useRef<HTMLButtonElement | null>(null);

    // Handle outside click of the hamburger menu
    useEffect(() => {
        let handler = (e: MouseEvent) => {

            // Check if the node object being clicked is a descendant of the node object referenced by 'menuRef'
            if (e.target instanceof Node && !menuRef.current?.contains(e.target) && e.target !== buttonRef.current) {

                let targetElement = e.target as Node | null; // Cast e.target to Node

                // Check if the clicked element or any of its ancestors is the button
                while (targetElement) {
                    if (targetElement === buttonRef.current) {
                        return; // Clicked inside the button, exit handler
                    }
                    targetElement = targetElement.parentNode;
                }
                
                // Close the hamburger menu
                setIsMenuOpen(false);
            }
        };

        // Listen on mouse click inside the document
        document.addEventListener('mousedown', handler);

        return() => {
            document.removeEventListener('mousedown', handler);
        };
    });

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

                {/* Shopping Cart Dropdown on Small Screen */}
                <div className="mr-5 md:hidden">
                    <Dropdown />
                </div>

                <div className="flex-none md:hidden">
                    <button ref={buttonRef} onClick={() => setIsMenuOpen(!isMenuOpen)} className="btn btn-ghost btn-square">
                        {/* Hamburger Menu Open Icon */}
                        <Bars3Icon className={"size-8 " + (isMenuOpen ? 'hidden' : 'block')} />
                        {/* Hamburger Menu Close Icon */}
                        <XMarkIcon className={"size-8 " + (isMenuOpen ? 'block' : 'hidden')} />
                    </button>
                </div>

                <div ref={menuRef} className={"absolute top-20 py-3 z-50 h-fit left-0 right-0 bg-slate-200 md:hidden " + (isMenuOpen ? 'block' : 'hidden')} >
                    <div className="flex flex-col items-center py-3 mx-3 bg-orange-600 rounded-md">
                        {user && (
                                <div className="w-full flex justify-start items-center gap-3 px-8 mb-3">
                                    {/* ------------------------------ PROFILE DROPDOWN ------------------------------ */}
                                    <div className="avatar">
                                        <div className="w-14 rounded-full">
                                            <img
                                                alt="Tailwind CSS Navbar component"
                                                src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                                        </div>
                                    </div>
                                    <span className="text-lg font-medium tracking-wide">
                                        { user.name }
                                    </span>
                                </div>
                            )}

                        {/* ------------------------------ SEARCH-BAR FOR PRODUCTS ------------------------------ */}
                        <form onSubmit={onSearchProduct} className="join flex-1 px-5">
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

                        {/* Sub-Navbar to display Products by Department */}
                        <div className="w-full mt-5">
                            <input type="checkbox" name="categoryCollapse" id="expandCollapse" 
                                className="peer sr-only"
                                checked={isCollapsed}
                            />
                            <label htmlFor="expandCollapse" 
                                className="flex justify-center"
                                onClick={() => setIsCollapsed(!isCollapsed)}
                            >
                                {
                                    isCollapsed ?
                                    (
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14" />
                                        </svg>
                                    ) :
                                    (
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    )
                                }
                                <span className="ml-1 font-semibold select-none">Category</span>
                            </label>
                            <div className="h-0 overflow-hidden peer-checked:h-fit peer-checked:overflow-scroll">
                                <ul className="flex flex-col [&>li]:text-slate-100 [&>li]:font-semibold gap-2 mt-1 items-center py-2 list-none rounded-md border-2 border-slate-200 mx-2">
                                    {departments.map((department) => (
                                        <li key={department.id} className={"tracking-wider " + (activeDept === department.slug ? '!text-slate-900 underline' : '')} >
                                            <Link href={route('product.byDepartment', department.slug)} onClick={() => setActiveDept(department.name)}>
                                                {department.name}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>

                        {user && (
                            <div className="w-full flex flex-col items-center [&>div]:w-full gap-1 mt-5 [&>div]:text-center [&>div]:py-2 [&>a]:w-full [&>a]:text-center [&>a]:py-2">
                                {is_admin_or_vendor && (
                                    <button type="button" onClick={handleVisitDashboard} className="justify-between hover:bg-slate-300/70">
                                        Dashboard
                                    </button>
                                )}
                                <Link href={route('profile.edit')} className="justify-between hover:bg-slate-100/70">
                                    Profile
                                </Link>
                                <Link href={route('logout')} method={"post"} className="hover:bg-slate-100/70">
                                    Logout
                                </Link>
                            </div>
                        )}

                        {!user && (
                            <div className="flex flex-col mt-3 w-full [&>a]:w-full gap-1 [&>a]:text-center [&>a]:py-2 [&>a]:tracking-wide [&>a]:text-slate-100 [&>a]:font-semibold [&>a]:text-lg">
                                <Link href={route('login')} className="hover:bg-slate-100/70 hover:text-primary">
                                    Login
                                </Link>
                                <Link href={route('register')} className="hover:bg-slate-100/70 hover:text-primary">
                                    Register
                                </Link>
                            </div>
                        )}                        
                    </div>
                </div>
                
                <div className="hidden mr-10 md:flex md:justify-center md:gap-4">
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
                                    <div className="w-16 rounded-full">
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