<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    // Place this widget on position 2 (based on the number widgets in the page)
    protected static ?int $sort = 2;

    // Set the column span of the widget
    protected int | string | array $columnSpan = [
                                                    '2xl' => 2,
                                                    'xl' => 3,
                                                    'default' => 'full',
                                                ];

    protected static string $view = 'filament.widgets.account-widget';
}
