<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Category;
use App\Models\Question;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-c-question-mark-circle';

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('questions');
    }

    public static function getModelLabel(): string
    {
        return __('question');
    }

    public static function getPluralModelLabel(): string
    {
        return __('questions');
    }

    public static function getPluralLabel(): string
    {
        return __('questions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('general'))
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Radio::make('level')
                                    ->options([
                                        1 => __('easy'),
                                        2 => __('medium'),
                                        3 => __('hard'),
                                    ])
                                    ->default(1)
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->required()
                                    ->label(__('difficulty level')),

                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'title')
                                    ->required()
                                    ->label(__('category')),

                                Forms\Components\TextInput::make('diff')
                                    ->nullable()
                                    ->label(__('difference')),
                            ]),
                    ]),

                Forms\Components\Section::make(__('question'))
                    ->schema([
                        Forms\Components\TextArea::make('question')
                            ->required()
                            ->maxLength(500)
                            ->label(__('question text')),

                        Forms\Components\FileUpload::make('question_media_url')
                            ->label(__('question media'))
                            ->disk('do')
                            ->directory('questions')
                            ->nullable()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    $set('question_media_url', null);
                                }
                            }),
                    ])->columnSpan(1),

                Forms\Components\Section::make(__('answer'))
                    ->schema([
                        Forms\Components\TextArea::make('answer')
                            ->required()
                            ->maxLength(500)
                            ->label(__('answer text')),

                        Forms\Components\FileUpload::make('answer_media_url')
                            ->label(__('answer media'))
                            ->disk('do')
                            ->directory('answers')
                            ->nullable()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    $set('answer_media_url', null);
                                }
                            }),
                    ])->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable()->label(__('id'))->toggleable(),
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
                Tables\Columns\TextColumn::make('category.title')->label(__('category')),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label(__('level'))
                    ->options([
                        1 => __('easy'),
                        2 => __('medium'),
                        3 => __('hard'),
                    ]),
                SelectFilter::make('category')
                    ->label(__('category'))
                    ->relationship('category', 'title')
                    ->options(Category::all()->pluck('title', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function query(): EloquentBuilder
    {
        return parent::query()->with(['category']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
