<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Filament\Resources\DepartmentResource\RelationManagers\CategoriesRelationManager;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                    $set('slug', Str::slug($state));
                })
                ->required(),
                TextInput::make('slug')
                    ->readOnly()
                    ->required(),
                Checkbox::make('active')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            CategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    /**
     * Only admin users can view this page
     * @return bool
     */
    public static function canViewAny(): bool {

        $user = Filament::auth()->user();

        return $user && $user->hasRole(RolesEnum::Admin);
    }
}
