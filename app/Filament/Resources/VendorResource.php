<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Vendor;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Enums\VendorStatusEnum;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VendorResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Mail\VendorApplicationStatus;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    // Store the navigation badge query
    protected static ?string $navigationBadge = null;

    // Display number of upending status
    public static function getNavigationBadge(): ?string
    {
        // Only run the query if the property is not set
        if(!isset(self::$navigationBadge))
        {
            self::$navigationBadge = static::getModel()::where('status', VendorStatusEnum::Pending->value)->count();
        }

        return self::$navigationBadge;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                        ->label('User Name')
                        ->searchable()
                        ->sortable(),
                TextColumn::make('store_name')
                        ->label('Store Name')
                        ->searchable()
                        ->sortable(),
                TextColumn::make('store_address')
                        ->label('Store Address')
                        ->searchable()
                        ->sortable(),
                TextColumn::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => 
                            // Creates an enum instance from provided $state value
                            VendorStatusEnum::tryFrom($state)?->getColor() ?? 'gray'
                        )
                        ->searchable()
                        ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                ->options(VendorStatusEnum::labels()),
            ])
            ->actions([
                Tables\Actions\Action::make('Approve')
                                    ->button()
                                    ->size(ActionSize::ExtraSmall)
                                    ->color('success')     
                                    ->disabled(fn ($record): bool => $record->status === VendorStatusEnum::Approved->value)
                                    ->action(function (Vendor $record) {
                                        $record->update(['status' => VendorStatusEnum::Approved->value]);

                                        Mail::to($record->user)
                                            ->queue(new VendorApplicationStatus("approved"));
                                    }),
                Tables\Actions\Action::make('Reject')
                                    ->button()
                                    ->size(ActionSize::ExtraSmall)
                                    ->color('danger')
                                    ->extraAttributes([
                                        'class' => 'mr-6',
                                    ])
                                    ->disabled(fn ($record): bool => $record->status === VendorStatusEnum::Rejected->value)
                                    ->action(function (Vendor $record) {
                                        $record->update(['status' => VendorStatusEnum::Rejected->value]);
                                        
                                        Mail::to($record->user)
                                            ->queue(new VendorApplicationStatus("rejected"));
                                    }),
                Tables\Actions\EditAction::make()
                                        ->iconButton()
                                        ->tooltip('Edit')
                                        ->fillForm(fn (Vendor $record): array => [
                                            'status' => $record->status,
                                            'store_name' => $record->store_name,
                                            'store_address' => $record->store_address,
                                        ])
                                        ->form([
                                            Select::make('status')
                                                    ->label('Status')
                                                    ->options(VendorStatusEnum::labels())
                                                    ->enum(VendorStatusEnum::class),
                                            TextInput::make('store_name')
                                                    ->label('Store Name')
                                                    ->minLength(2)
                                                    ->maxLength(255)
                                                    ->autofocus(),
                                            TextInput::make('store_address')
                                                    ->label('Store Address')
                                                    ->minLength(10)
                                                    ->maxLength(255),
                                        ])
                                        ->action(function (array $data, Vendor $record): void
                                        {
                                            // Update user's data
                                            $record->status = $data['status'];
                                            $record->store_name = $data['store_name'];
                                            $record->store_address = $data['store_address'];

                                            $record->save();

                                            if ($record->wasChanged()) {
                                                Notification::make()
                                                            ->title('Updated vendor')
                                                            ->success()
                                                            ->send();
                                            }
                                        }),
                // Tables\Actions\DeleteAction::make()
                //                         ->iconButton()
                //                         ->tooltip('Delete')
                //                         ->action(fn (Vendor $record) => $record->delete())
                //                         ->requiresConfirmation(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVendors::route('/'),
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
