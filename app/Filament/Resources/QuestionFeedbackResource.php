<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionFeedbackResource\Pages;
use App\Filament\Resources\QuestionFeedbackResource\RelationManagers;
use App\Models\QuestionFeedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionFeedbackResource extends Resource
{
    protected static ?string $model = QuestionFeedback::class;

    protected static ?string $navigationIcon = 'heroicon-m-chat-bubble-bottom-center-text';

    public static function getModelLabel(): string
    {
        return __('qeustion feedback');
    }

    public static function getPluralModelLabel(): string
    {
        return __('qeustion feedbacks');
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
                Forms\Components\Select::make('user_id')
                    ->label(__('provided by'))
                    ->relationship('user', 'name') // Assuming you want to select the user providing feedback
                    ->required(),
                Forms\Components\Select::make('question_id')
                    ->label(__('question'))
                    ->relationship('question', 'question')
                    ->required(),
                Forms\Components\Textarea::make('feedback')
                    ->label(__('feedback'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.question')
                    ->label(__('question'))
                    ->limit(30)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('feedback')
                    ->label(__('feedback'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('provided by'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('provided on'))->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListQuestionFeedback::route('/'),
            // 'create' => Pages\CreateQuestionFeedback::route('/create'),
            // 'view' => Pages\ViewQuestionFeedback::route('/{record}'),
            // 'edit' => Pages\EditQuestionFeedback::route('/{record}/edit'),
        ];
    }
}
