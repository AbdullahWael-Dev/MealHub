<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->alphaDash(),

                        Select::make('type')
                            ->options([
                                'percentage' => 'Percent',
                                'fixed' => 'Fixed',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(fn (callable $get) => $get('type') === 'percent' ? '%' : null),

                        TextInput::make('min_order_amount')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('max_discount')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->helperText('Optional cap on the discount amount for percent coupons.'),

                        TextInput::make('usage_limit')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        TextInput::make('used_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Section::make('Validity')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->required()
                            ->after('starts_at'),

                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
