<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use App\Enums\ProductStatusEnum;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ProductImages;
use App\Filament\Resources\ProductResource\Pages\ProductVariations;
use App\Filament\Resources\ProductResource\Pages\ProductVariationTypes;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-s-queue-list';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->belongsToVendor(auth()->user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    TextInput::make('title')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, callable $set) {
                            $set('slug', Str::slug($state));
                        })
                        ->required(),
                    TextInput::make('slug')
                        ->readOnly()
                        ->required(),
                    Select::make('department_id')
                        ->relationship('department', 'name')
                        ->label(__('Department'))
                        ->preload()
                        ->searchable()
                        ->required()
                        ->reactive() // Whenever a department changes, the callback below resets the category ID to null
                        ->afterStateUpdated(function (callable $set) {
                            $set('category_id', null);
                        }),
                    Select::make('category_id')
                        ->relationship(
                            'category',
                            'name',
                            function (Builder $query, callable $get) {
    
                                // Modify the category query based on the selected department
                                $departmentId = $get('department_id'); // Get the selected department
    
                                if ($departmentId) {
                                    // Filters categories based on the department ID
                                    $query->where('department_id', $departmentId);
                                }
                            }
                        )
                        ->label(__('Category'))
                        ->preload()
                        ->searchable()
                        ->required(),
                ]),
                RichEditor::make('description')
                ->required()
                ->toolbarButtons([
                    'blockquote',
                    'bold',
                    'bulletList',
                    'h2',
                    'h3',
                    'italic',
                    'link',
                    'orderedList',
                    'redo',
                    'strike',
                    'underLine',
                    'undo',
                    'table',
                ])
                ->columnSpan(2),
                TextInput::make('price')
                    ->required()
                    ->numeric(),
                TextInput::make('quantity')
                    ->integer(),
                Select::make('status')
                    ->options(ProductStatusEnum::labels())
                    ->default(ProductStatusEnum::Draft->value)
                    ->required(),
                Section::make('SEO')
                    ->collapsible()
                    ->schema([
                        TextInput::make('meta_title'),
                        Textarea::make('meta_description'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(components: [
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->limit(1)
                    ->label('Thumbnail')
                    ->conversion('thumb'),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->words(10),
                TextColumn::make('status')
                    ->badge()
                    ->colors(ProductStatusEnum::colors()),
                TextColumn::make('department.name'),
                TextColumn::make('category.name'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProductStatusEnum::labels()),
                SelectFilter::make('department_id')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'images' => Pages\ProductImages::route('/{record}/images'),
            'variation-types' => Pages\ProductVariationTypes::route('/{record}/variation-types'),
            'product-variations' => Pages\ProductVariations::route('/{record}/product-variations'),
        ];
    }

    // Add a sub-navigation to edit the product resource
    public static function getRecordSubNavigation(Page $page): array {

        return $page->generateNavigationItems([
            EditProduct::class,
            ProductImages::class,
            ProductVariationTypes::class,
            ProductVariations::class,
        ]);
    }

    /**
     * Only vendor users can view this page
     * @return bool
     */
    public static function canViewAny(): bool {

        $user = Filament::auth()->user();

        return $user && $user->hasRole(RolesEnum::Vendor);
    }
}
