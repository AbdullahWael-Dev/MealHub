<?php

namespace App\Filament\Resources\Addresses\Schemas;

use App\Models\Address;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Address Owner & Type')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit') 
                            ->columnSpan(1),

                        Select::make('title')
                            ->label('Address Label')
                            ->options([
                                'Home' => 'Home',
                                'Work' => 'Work',
                                'Other' => 'Other',
                            ])
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Recipient Info')
                    ->schema([
                        TextInput::make('recipient_name')
                            ->label('Recipient Name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Location Details')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('city')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('area')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('street')
                                ->required()
                                ->maxLength(150),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('building')
                                ->maxLength(50),

                            TextInput::make('floor')
                                ->maxLength(20),

                            TextInput::make('apartment')
                                ->maxLength(20),
                        ]),

                        TextInput::make('landmark')
                            ->label('Nearby Landmark')
                            ->maxLength(150)
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('latitude')
                                ->numeric()
                                ->rules(['nullable', 'numeric', 'between:-90,90']),

                            TextInput::make('longitude')
                                ->numeric()
                                ->rules(['nullable', 'numeric', 'between:-180,180']),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('Additional Info')
                    ->schema([
                        Textarea::make('notes')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_default')
                            ->label('Set as default address')
                            ->helperText(
                                fn (?Address $record) => $record?->is_default
                                    ? 'This is currently the default address for this user.'
                                    : 'Turning this on will automatically unset the default flag on any other address belonging to this user.'
                            )
                            ->disabled(fn (?Address $record) => (bool) $record?->is_default)
                            ->dehydrated(fn (?Address $record) => ! $record?->is_default)
                            ->default(false),
                    ]),
            ]);
    }
}