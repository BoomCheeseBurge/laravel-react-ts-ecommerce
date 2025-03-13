<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                        ->label('Full Name')
                        ->minLength(2)
                        ->maxLength(255)
                        ->autofocus()
                        ->required(),
                TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->unique()
                        ->required(),
                Select::make('role')
                        ->label('Role')
                        ->options(RolesEnum::labels())
                        ->enum(RolesEnum::class)
                        ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Email Not Verified'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                                        ->iconButton()
                                        ->tooltip('Edit')
                                        ->disabled(fn ($record): bool => (($record->id === auth()->user()->id) || (User::role(RolesEnum::Admin)->count() < 2)) && $record->hasRole(RolesEnum::Admin))
                                        ->fillForm(fn (User $record): array => [
                                            'name' => $record->name,
                                            'email' => $record->email,
                                            'role' => $record->getRoleNames()->first(),
                                        ])
                                        ->form([
                                            TextInput::make('name')
                                                    ->label('Full Name')
                                                    ->minLength(2)
                                                    ->maxLength(255)
                                                    ->autofocus(),
                                            TextInput::make('email')
                                                    ->label('Email Address')
                                                    ->email()
                                                    ->unique(ignoreRecord: true),
                                            TextInput::make('password')
                                                    ->label('Password')
                                                    ->password()
                                                    ->revealable()
                                                    ->confirmed(),
                                            TextInput::make('password_confirmation')
                                                    ->label('Confirm Password'),
                                            Select::make('role')
                                                    ->label('Role')
                                                    ->options(RolesEnum::labels())
                                                    ->enum(RolesEnum::class),
                                        ])
                                        ->action(function (array $data, User $record): void
                                        {
                                            // Update user's data
                                            $record->name = $data['name'];
                                            $record->email = $data['email'];

                                            // Hash and update password (if provided)
                                            if (!empty($data['password'])) {
                                                $record->password = bcrypt($data['password']);
                                            }

                                            // Assign role
                                            $record->assignRole($data['role']); // Assuming you're using Spatie Roles

                                            $record->save();

                                            if ($record->wasChanged()) {
                                                Notification::make()
                                                            ->title('Updated user')
                                                            ->success()
                                                            ->send();
                                            }
                                        }),
                Tables\Actions\DeleteAction::make()
                                        ->iconButton()
                                        ->disabled(fn ($record): bool => $record->hasRole(RolesEnum::Admin))
                                        ->tooltip('Delete')
                                        ->action(fn (User $record) => $record->delete())
                                        ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
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
