<?php

namespace App\Filament\Resources;

use App\Enums\QuestionMediaTypeEnum;
use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Category;
use App\Models\Question;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-c-question-mark-circle';

    public static function getModelLabel(): string
    {
        return __('question');
    }

    public static function getNavigationLabel(): string
    {
        return __('questions');
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
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label(__('category')),

                                Forms\Components\TextInput::make('diff')
                                    ->nullable()
                                    ->label(__('difference')),

                                // options
                                Forms\Components\Toggle::make('options.hide_media')
                                    ->label(__('hide question media'))
                                    ->helperText('hide question media for (n) seconds, or until video/audio ends')
                                    ->inline(false)
                                    ->inlineLabel(false)
                                    ->default(false),

                                Forms\Components\TextInput::make('options.hide_media_after')
                                    ->label(__('hide question media after (seconds)'))
                                    ->helperText('leave empty it empty if you want the media hide automatically (works for video/audio only)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Enter time in seconds'),
                            ]),
                    ]),

                Forms\Components\Section::make(__('question'))
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->required()
                            ->maxLength(255)
                            ->label(__('question text')),

                        // Forms\Components\ToggleButtons::make('question_media_type')
                        //     ->options(QuestionMediaTypeEnum::class)
                        //     ->enum(QuestionMediaTypeEnum::class)
                        //     ->default(QuestionMediaTypeEnum::TEXT->value)
                        //     ->inline()
                        //     ->inlineLabel(false)
                        //     ->required()
                        //     ->label(__('question media type'))
                        //     ->reactive()
                        //     ->disabled(fn($get) => $get('question_media_url') != null),

                        Forms\Components\FileUpload::make('question_media_url')
                            ->label(__('question media'))
                            ->disk('do')
                            ->directory('questions')
                            ->nullable()
                            ->reactive()
                            // ->hidden(fn($get) => $get('question_media_type') == QuestionMediaTypeEnum::TEXT->value)
                            ->acceptedFileTypes(['image/*', 'video/*', 'audio/*']),

                    ])->columnSpan(1),

                Forms\Components\Section::make(__('answer'))
                    ->schema([
                        Forms\Components\TextInput::make('answer')
                            ->required()
                            ->maxLength(255)
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
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label(__('id'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('question')->searchable()->label(__('question'))
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('answer')->searchable()->label(__('answer')),
                Tables\Columns\TextColumn::make('level')
                    ->sortable()
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('user_id');
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
