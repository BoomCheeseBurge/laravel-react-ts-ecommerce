
function ParcelIcon(props: any) {
    return (
        <svg
            version="1.1"
            id="Icons"
            xmlns="http://www.w3.org/2000/svg"
            xmlnsXlink="http://www.w3.org/1999/xlink"
            viewBox="0 0 32 32"
            xmlSpace="preserve"
            fill={props.fill || "currentColor"} // Use prop for fill, default to currentColor
            stroke={props.stroke || "currentColor"} // Use prop for stroke, default to currentColor
            strokeWidth={props.strokeWidth || 2} // Use prop for strokeWidth, default to 2
            strokeLinecap={props.strokeLinecap || "round"} // Use prop for strokeLinecap, default to round
            strokeLinejoin={props.strokeLinejoin || "round"} // Use prop for strokeLinejoin, default to round
            strokeDasharray={props.strokeDasharray} // Pass through strokeDasharray prop
            strokeMiterlimit="10"
            {...props} // Pass through any other props you might need
        >
            <g id="SVGRepo_bgCarrier" strokeWidth="0" />
            <g id="SVGRepo_tracerCarrier" strokeLinecap="round" strokeLinejoin="round" />
            <g id="SVGRepo_iconCarrier">
            <path
                className="st0"
                d="M7,12V7c0-1.1,0.9-2,2-2h5v5h8V5h5c1.1,0,2,0.9,2,2v18c0,1.1-0.9,2-2,2H9c-1.1,0-2-0.9-2-2v-5"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
                strokeDasharray={props.strokeDasharray}
            />
            <line
                className="st0"
                x1="22"
                y1="20"
                x2="22"
                y2="14"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <polyline
                className="st0"
                points="24,16.1 22,14 20,16.1 "
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <line
                className="st0"
                x1="20"
                y1="23"
                x2="24"
                y2="23"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <line
                className="st0"
                x1="22"
                y1="5"
                x2="14"
                y2="5"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <line
                className="st0"
                x1="4"
                y1="12"
                x2="12"
                y2="12"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <line
                className="st0"
                x1="4"
                y1="20"
                x2="12"
                y2="20"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <line
                className="st0"
                x1="2"
                y1="16"
                x2="10"
                y2="16"
                fill="none"
                stroke="inherit"
                strokeWidth="inherit"
                strokeLinecap="inherit"
                strokeLinejoin="inherit"
                strokeMiterlimit="inherit"
            />
            <rect x="-72" y="-504" className="st3" width="536" height="680" fill="none" />
            </g>
        </svg>
    );
}

export default ParcelIcon;