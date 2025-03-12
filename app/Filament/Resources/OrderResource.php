<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\OrderStatusEnum;
use Filament\Facades\Filament;
use Illuminate\Support\Number;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Filters\Indicator;
use Filament\Infolists\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Components\Split;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Infolists\Components\OrderItems;
use Filament\Infolists\Components\Group;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    // Place this nav item on position 3 (based on the number nav items in the page)
    protected static ?int $navigationSort = 3;

    // Store the navigation badge query
    protected static ?string $navigationBadge = null;

    // Display number of order that has not been delivered or shipped
    public static function getNavigationBadge(): ?string
    {
        // Only run the query if the property is not set
        if(!isset(self::$navigationBadge))
        {
            self::$navigationBadge = static::getModel()::where('vendor_user_id', auth()->user()->id)
                                            ->whereNot(function (Builder $query) {
                                                $query->whereIn('status', [OrderStatusEnum::Delivered, OrderStatusEnum::Shipped]);
                                            })
                                            ->count();
        }

        return self::$navigationBadge;
    }

    // Determine if the user can create a new record
    public static function canCreate(): bool
    {
        return false;
    }

    // Get the orders that belongs to this vendor only
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->belongsToVendor(auth()->user()->id);
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
                TextColumn::make('id')
                        ->label('Order No.')
                        ->searchable()
                        ->sortable(),
                TextColumn::make('vendor_subtotal')
                        ->label('Subtotal')
                        ->formatStateUsing(fn (?float $state): string => isset($state) ? Number::currency($state) : _("N/A"))
                        ->searchable()
                        ->sortable(),
                TextColumn::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'draft' => 'gray',
                            'paid' => 'info',
                            'shipped' => 'success',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                        })
                        ->searchable()
                        ->sortable(),
                TextColumn::make('created_at')
                        ->label('Order Date')
                        ->date(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                                ->placeholder(fn ($state) : string => Carbon::createFromTimestamp(0)->format('Y-m-d')),
                        DatePicker::make('created_until')
                                ->placeholder(fn ($state) : string => now()->format('Y-m-d')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {

                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = Indicator::make('Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                                                    ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = Indicator::make('Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                                                    ->removeField('created_until');
                        }
 
                        return $indicators;
                    }),
                SelectFilter::make('status')
                ->options(OrderStatusEnum::labels()),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('Details')
                // This is the important part!
                ->infolist([
                    // Inside, we can treat this as any info list and add all the fields we want
                    Section::make('Order Information')
                        ->schema([
                            TextEntry::make('id')
                                    ->label('Order No.'),
                            TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'paid' => 'info',
                                        'shipped' => 'success',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                    }),
                            TextEntry::make('vendor_subtotal')
                                    ->label('Sub-Total')
                                    ->formatStateUsing(fn (float $state): string => __(Number::currency($state))),
                            TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label('Created At'),
                        ])
                        ->columns(),
                    Section::make('Customer Details')
                        ->schema([
                            TextEntry::make('user.name')
                                    ->label('Username'),
                            TextEntry::make('user.email')
                                    ->label('Email Address'),
                        ])
                        ->columns(),
                ])
                ->modalWidth(MaxWidth::Large) // Set modal width (required)
                ->modalSubmitAction(false) // Hide "Submit"
                ->modalCancelAction(false), // Hide "Cancel"
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    // Infolist will be automatically shown in the view page
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                ->schema([
                    Split::make([
                        Grid::make(2)
                        ->schema([
                            Group::make([
                                TextEntry::make('id')
                                        ->label('Order No.')
                                        ->color('primary'),
                                TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'draft' => 'gray',
                                            'paid' => 'info',
                                            'shipped' => 'success',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                        }),
                            ]),
                            Group::make([
                                TextEntry::make('vendor_subtotal')
                                        ->label('Sub-Total')
                                        ->formatStateUsing(fn (float $state): string => __(Number::currency($state))),
                                TextEntry::make('created_at')
                                        ->dateTime()
                                        ->label('Created At'),
                            ]),
                        ]),
                    ])->from('lg')
                ]),

                Section::make('Customer Details')
                ->schema([
                    TextEntry::make('user.name')
                            ->label('Username'),
                    TextEntry::make('user.email')
                            ->label('Email Address'),
                ])
                ->columns()
                ->collapsible(),

                Section::make('Order Items')
                ->schema([
                    OrderItems::make(''),
                ])
                ->collapsible(),
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
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
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
