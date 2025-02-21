import { Image } from "@/types";
import { useEffect, useState } from "react";

function Carousel({ images }: { images: Image[] }) {

    const [selectedImage, setSelectedImage] = useState<Image>(images[0]);

    // Update the selected image if there is any change to the 'images' prop
    useEffect(() => {
      
        setSelectedImage(images[0]);
    
    //   return () => {}
    }, [images]);

    return (
        <>
            <div className="flex items-start gap-8">
                {/* Small thumbnail product variations */}
                <div className="flex flex-col items-center gap-2 py-2">
                    {images.map((image, i) => (

                        <button onClick={() => setSelectedImage(image)} 
                            key={image.id} 
                            className={'border-2' + (selectedImage.id === image.id ? 'border-blue-500' : 'hover:border-blue-500')}
                        >
                            <img src={image.thumb} alt="Thumb Image" className="w-[50px]" />
                        </button>
                    ))}
                </div>

                {/* Single image display */}
                <div className="carousel w-full">
                    <div className="carousel-item w-full">
                        <img src={selectedImage.large} alt="Large Product Image" className="w-full" />
                    </div>
                </div>
            </div>
        </>
    );
}

export default Carousel;