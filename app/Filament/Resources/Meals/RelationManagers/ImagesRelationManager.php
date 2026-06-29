<?php

namespace App\Filament\Resources\Meals\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory('meal-images')
                    ->required()
                    ->imageEditor()
                    ->columnSpanFull(),

                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_primary')
                    ->label('Primary Image'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image_path')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public'),

                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->limit(30),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),

                ToggleColumn::make('is_primary')
                    ->label('Primary'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}