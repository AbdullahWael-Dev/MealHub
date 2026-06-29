<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Meal')
                    ->columnSpanFull()
                    ->tabs([

                        Tab::make('General')
                            ->icon('heroicon-o-information-circle')
                            ->components([
                                Section::make()
                                    ->columns(2)
                                    ->components([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(150)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('slug', Str::slug($state));
                                            })
                                            ->columnSpan(1),

                                        Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(1),

                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(180)
                                            ->unique(ignoreRecord: true)
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Automatically generated from the name')
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Status')
                                    ->description('Control how this meal appears to customers')
                                    ->columns(2)
                                    ->components([
                                        Toggle::make('is_available')
                                            ->label('Available for order')
                                            ->helperText('Customers can order this meal')
                                            ->default(true)
                                            ->inline(false),

                                        Toggle::make('is_featured')
                                            ->label('Featured meal')
                                            ->helperText('Shown in the featured section')
                                            ->default(false)
                                            ->inline(false),
                                    ]),
                            ]),

                        Tab::make('Pricing & Stock')
                            ->icon('heroicon-o-currency-dollar')
                            ->components([
                                Section::make('Pricing')
                                    ->columns(2)
                                    ->components([
                                        TextInput::make('price')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->minValue(0),

                                        TextInput::make('discount_price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->minValue(0)
                                            ->lt('price')
                                            ->helperText('Must be lower than the original price'),
                                    ]),

                                Section::make('Inventory')
                                    ->columns(2)
                                    ->components([
                                        TextInput::make('stock_quantity')
                                            ->label('Stock quantity')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),

                                        TextInput::make('preparation_time')
                                            ->label('Preparation time')
                                            ->numeric()
                                            ->suffix('minutes')
                                            ->helperText('Time to prepare this meal'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}