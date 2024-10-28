<?php

namespace App\Filament\Resources\Store;

use App\Filament\Resources\Store\StoreCategoryResource\Pages;
use App\Filament\Resources\Store\StoreCategoryResource\RelationManagers;
use App\Models\Store\StoreCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoreCategoryResource extends Resource
{
    protected static ?string $model = StoreCategory::class;

    protected static ?int $navigationSort = 200;

    public static function getNavigationLabel(): string
    {
        return __('categories');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('store');
    }
    protected static ?string $navigationIcon = 'heroicon-m-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\Textarea::make('description'),
                Forms\Components\FileUpload::make('image')
                    ->disk('do')
                    ->directory('store/categories'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\ImageColumn::make('image')->disk('do'),
                Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreCategories::route('/'),
            // 'create' => Pages\CreateStoreCategory::route('/create'),
            // 'edit' => Pages\EditStoreCategory::route('/{record}/edit'),
        ];
    }
}
