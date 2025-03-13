<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use App\Filament\Resources\UserResource;
use App\Services\InitialPasswordService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected ?string $subheading = 'ℹ️ All actions are performed in modals';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                                ->action(function (array $data, InitialPasswordService $initialPasswordService) {

                                    // Persist the user record to the database
                                    $user = static::getModel()::create([
                                        'name' => $data['name'],
                                        'email' => $data['email'],
                                        'password' => bcrypt(Str::random(20)),
                                    ]); 
                            
                                    
                                    // Send an email to the user to enable them to fill in their own password
                                    $status = $initialPasswordService->sendResetLink(['email' => $user->email]);

                                    // Send a notification that an email has been sent to the user
                                    Notification::make()
                                                ->title('User created')
                                                ->body(__($status))
                                                ->success()
                                                ->send();
                            
                                    // Assign the selected role of the user
                                    $user->assignRole($data['role']);
                            
                                    return $user;
                                }),
        ];
    }
}
