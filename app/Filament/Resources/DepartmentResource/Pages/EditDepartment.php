<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string {

        /**
         * Since this file is uses the department resource, 
         * the getResource() method will get the corresponding resource class.
         * Then, there is the getPages() method in that resource file to which the URL is fetched from.
         */
        return $this->getResource()::getUrl('index');
    }
}
