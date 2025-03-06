<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array {

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string {

        /**
         * Since this file is uses the department resource, 
         * the getResource() method will get the corresponding resource class.
         * Then, there is the getPages() method in that resource file to which the URL is fetched from.
         */
        return $this->previousUrl;
    }
}
