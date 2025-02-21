import { Product, VariationTypeOption } from "@/types";

function ProductVariationTypes({ 
    product, 
    selectedOptions, 
    chooseOption 
}: { 
    product: Product, 
    selectedOptions: Record<number, VariationTypeOption>, 
    chooseOption: (typeId: number, option: VariationTypeOption, updateRouter?: boolean) => void 
}) {
    return (
        <div className="mb-6">
            {
                product.variationTypes.map((type, i) => (

                    <div key={type.id} className="my-4">
                        
                        <div className="mb-2">
                            <b>{type.name}</b>
                        </div>

                        {/* Handle in case of variation type 'image' */}
                        {type.type === 'Image' && (

                            <div className="flex gap-3 mb-4">
                                {type.options.map(option => (

                                    <div key={option.id} onClick={() => chooseOption(type.id, option)} >

                                        {option.images && (
                                            <img src={option.images[0].thumb} 
                                            alt="Variation Type Option" 
                                            className={'w-[50px] ' + (selectedOptions[type.id]?.id === option.id ? 'outline outline-2 outline-primary rounded-sm' : '')} // Check the current loop option is equal to the selected option, then give it a special CSS
                                            />
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* Handle in case of variation type 'radio' */}
                        {type.type === 'Radio' && (
                            
                            <div className="join flex mb-4">
                                
                                {type.options.map(option => (

                                    <input key={option.id} onChange={() => chooseOption(type.id, option)} 
                                        className="btn join-item"
                                        type="radio"
                                        value={option.id}
                                        checked={selectedOptions[type.id]?.id === option.id}
                                        name={'variation_type_' + type.id}
                                        aria-label={option.name}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                ))
            }
        </div>
    );
}

export default ProductVariationTypes;