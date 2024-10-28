<?php

namespace App\Filament\Resources\Store;

use App\Filament\Resources\Store\StoreProductResource\Pages;
use App\Models\Store\StoreProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoreProductResource extends Resource
{
    protected static ?string $model = StoreProduct::class;

    protected static ?int $navigationSort = 205;

    public static function getNavigationLabel(): string
    {
        return __('products');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('store');
    }

    protected static ?string $navigationIcon = 'heroicon-m-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label(__('Description'))
                    ->maxLength(65535),

                Forms\Components\Select::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'title')
                    ->required(),

                Forms\Components\FileUpload::make('image')
                    ->label(__('Image'))
                    ->disk('do')
                    ->directory('store/products')
                    ->image(),

                Forms\Components\TextInput::make('price')
                    ->label(__('Price'))
                    ->required()
                    ->numeric()
                    ->prefix(__('kwd'))
                    ->minValue(0)
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('do')
                    ->label(__('Image'))
                    ->size(50),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.title')
                    ->label(__('Category'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('KWD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                // Add any filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        // Define any relations if needed
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreProducts::route('/'),
            // 'create' => Pages\CreateStoreProduct::route('/create'),
            // 'edit' => Pages\EditStoreProduct::route('/{record}/edit'),
        ];
    }
}
