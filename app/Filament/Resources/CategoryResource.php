<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers\QuestionsRelationManager;
use App\Imports\QuestionsImport;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 10;

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
    public static function getNavigationLabel(): string
    {
        return __('categories');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label(__('title'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('content')->label(__('content'))
                    ->maxLength(65535),
                FileUpload::make('image')->label(__('image'))
                    ->image()
                    // ->imageEditor()
                    ->disk('do')
                    ->directory('categories')
                    ->visibility('public'),
                Toggle::make('is_temp')->label(__('temp category'))->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label(__('image'))->disk('do')->circular(),
                TextColumn::make('title')->label(__('title'))->sortable()->searchable(),
                TextColumn::make('questions_count')
                    ->label(__('number of questions'))
                    ->counts('questions')  // Use the relationship method to count questions
                    ->sortable(),
                TextColumn::make('is_temp')
                    ->label(__('temp category'))
                    ->formatStateUsing(function ($state) {
                        return $state ? __('yes') : __('no');
                    })
                    ->color(function ($state) {
                        return $state ? 'warning' : 'primary';
                    }),

            ])
            ->filters([
                Filter::make('is_temp')
                    ->label(__('temp category'))
                    ->query(fn(Builder $query): Builder => $query->where('is_temp', true))
                    ->toggle()

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Tables\Actions\Action::make('importQuestions')
                //     ->label(__('import questions'))
                //     ->icon('heroicon-o-arrow-up-tray')
                //     ->form([
                //         FileUpload::make('file')
                //             ->label(__('excel file (.xlsx)'))
                //             ->required()
                //             ->disk('local')
                //             ->directory('imports')
                //             ->storeFiles(false)
                //             ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                //     ])
                //     ->action(function (array $data, $record) {
                //         $file = $data['excel_file'];
                //         $path = $file->store('imports');

                //         $import = new QuestionsImport(
                //             $record->id,
                //             $data['excel_column_mapping']
                //         );

                //         // Queue the import job
                //         Excel::queueImport($import, Storage::disk('local')->path($path))
                //             ->allOnQueue('imports'); // Optional: specify a queue

                //         // Display Notification
                //         Notification::make()
                //             ->title(__('Questions import has been queued'))
                //             ->success()
                //             ->send();

                //         $path = $data['file']->store('imports');

                //         $import = new QuestionsImport($record->id);
                //         Excel::import($import, Storage::disk('local')->path($path));
                //         $importedRows = $import->getimportedRows();
                //         Storage::disk('local')->delete($path);


                //         Notification::make()
                //             ->title($importedRows . " " . __('questions imported successfully'))
                //             ->success()
                //             ->send();
                //     }),

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
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
