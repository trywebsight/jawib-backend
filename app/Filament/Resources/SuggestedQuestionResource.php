<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuggestedQuestionResource\Pages;
use App\Filament\Resources\SuggestedQuestionResource\RelationManagers;
use App\Models\SuggestedQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuggestedQuestionResource extends Resource
{
    protected static ?string $model = SuggestedQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function getModelLabel(): string
    {
        return __('suggested question');
    }

    public static function getPluralModelLabel(): string
    {
        return __('suggested questions');
    }

    public static function getNavigationLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function getPluralLabel(): string
    {
        return self::getPluralModelLabel();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('id')),
                Tables\Columns\TextColumn::make('question')
                    ->label(__('question'))
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.title')
                    ->label(__('category'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('suggested by')),
                Tables\Columns\ImageColumn::make('images')
                    ->label(__('images'))
                    ->circular()
                    ->disk('do')
                    ->stacked()
                    ->toggleable(),
                // Tables\Columns\ImageColumn::make('images'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created at'))
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListSuggestedQuestions::route('/'),
            // 'create' => Pages\CreateSuggestedQuestion::route('/create'),
            // 'edit' => Pages\EditSuggestedQuestion::route('/{record}/edit'),
        ];
    }
}
