<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getModelLabel(): string
    {
        return __('page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pages');
    }

    public static function getNavigationLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function getPluralLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('title'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slug')
                    ->label(__('slug'))
                    ->required()
                    ->prefix(env("FRONTEND_URL") . "/pages")
                    ->maxLength(255)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state)))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->label(__('content'))
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->label(__('is published'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('title')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('slug')),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('is published'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('published')
                    ->label(__('published'))
                    ->query(fn($query) => $query->where('is_published', true))
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            // 'view' => Pages\ViewPage::route('/{record}'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
