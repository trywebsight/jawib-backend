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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('question')->limit(50),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Category')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')->label('Suggested By'),
                Tables\Columns\ImageColumn::make('images')
                    ->circular()
                    ->disk('do')
                    ->stacked()
                    ->toggleable(),
                // Tables\Columns\ImageColumn::make('images'),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->since(),
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
