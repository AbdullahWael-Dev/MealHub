<?php

namespace App\Filament\Resources\Users\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->description('Basic details used to identify and contact this user.')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->components([
                        FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->placeholder('e.g. John Doe')
                            ->prefixIcon('heroicon-o-user')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->placeholder('name@example.com')
                            ->prefixIcon('heroicon-o-envelope')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->placeholder('e.g. 01012345678')
                            ->prefixIcon('heroicon-o-phone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->columnSpan(1),
                    ]),

                Section::make('Security')
                    ->description('Set a password and control login access.')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->components([
                        TextInput::make('password')
                            ->label('Password')
                            ->placeholder('Leave blank to keep current password')
                            ->prefixIcon('heroicon-o-key')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->maxLength(255)
                            ->columnSpan(1),

                        Select::make('roles')
                            ->label('Role')
                            ->placeholder('Select a role')
                            ->prefixIcon('heroicon-o-shield-check')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Account Active')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->default(true)
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}