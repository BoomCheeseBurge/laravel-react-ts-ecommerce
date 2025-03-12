<?php

namespace App\Filament\Pages;

use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
 
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getColumns(): int|string|array
    {
        return 6;
    }
 
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('filterBy')
                            ->options([
                                'week' => 'week',
                                'month' => 'month',
                                'year' => 'year',
                                ])
                            ->label('Filter stats by this')
                            ->default('week')
                            ->selectablePlaceholder(false)
                    ])
                    ->columnSpan([
                        '2xl' => 2,
                        'xl' => 1,
                        'default' => 1,
                    ])
                    ->columnStart(
                        [
                            '2xl' => 2,
                            'xl' => 1
                        ]
                    )
                    ->hidden(fn (): bool => auth()->user()->hasRole(RolesEnum::Admin)),
            ]);
    }
}