<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductVariationTypeEnum;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class ProductVariationTypes extends EditRecord
{
    protected static string $resource = ProductResource::class;

    // Override the title
    protected static ?string $title = "Variation Types";

    // Override the default hero icon
    protected static ?string $navigationIcon = 'heroicon-c-squares-plus';

    public function form(Form $form): Form {

        return $form->schema([
            /**
             * For the size, color, etc
             */
            Repeater::make('variationTypes')
                    ->label(false)
                    ->relationship()
                    ->collapsible()
                    ->defaultItems(1)
                    ->addActionLabel('Add new variation type')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Select::make('type')
                            ->live()
                            ->options(ProductVariationTypeEnum::labels())
                            ->required(),
                        /**
                         * For the options of size and color like small, red, etc
                         */
                        Repeater::make('options')
                            ->relationship()
                            ->collapsible()
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpan(2)
                                    ->required(),
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->panelLayout('grid')
                                    ->collection('images')
                                    ->reorderable()
                                    ->appendFiles()
                                    ->preserveFilenames()
                                    ->columnSpan(3)
                                    ->hidden(fn (Get $get): bool => $get('../../type') !== 'Image')
                            ]),
                    ])
    ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
