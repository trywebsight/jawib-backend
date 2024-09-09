<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public static function getModelLabel(): string
    {
        return __('question');
    }

    public static function getPluralLabel(): string
    {
        return __('questions');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable()->label(__('#id'))->toggleable(),
                Tables\Columns\TextColumn::make('question')->searchable()->label(__('question')),
                Tables\Columns\TextColumn::make('answer')->searchable()->label(__('answer')),
                Tables\Columns\TextColumn::make('level')
                    ->label(__('level'))
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            1 => __('easy'),
                            2 => __('medium'),
                            3 => __('hard'),
                            default => __('unknown'),
                        };
                    }),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label(__('level'))
                    ->options([
                        1 => __('easy'),
                        2 => __('medium'),
                        3 => __('hard'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
