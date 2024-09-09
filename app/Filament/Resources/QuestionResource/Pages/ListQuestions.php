<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Imports\QuestionsImport;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Action::make('import')
                ->label(__('import questions'))
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->searchable()
                        ->options(Category::all()->pluck('title', 'id'))
                        ->required(),
                    Forms\Components\FileUpload::make('excel_file')
                        ->label('Excel File')
                        ->disk('local')
                        ->directory('imports')
                        ->storeFiles(false)
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                $headers = static::getExcelHeaders($state);
                                $set('excel_headers', $headers);
                            }
                        }),
                    Forms\Components\Fieldset::make('Column Mappings')
                        ->label(__('column mappings'))
                        ->schema(function (callable $get) {
                            $headers = $get('excel_headers') ?? [];

                            return static::excelColumnsMapping($headers);
                        })
                        ->visible(false), // Only visible if headers are loaded
                    // ->visible(fn(callable $get) => !empty($get('excel_headers'))), // Only visible if headers are loaded
                ])
                ->action(function (array $data) {
                    try {
                        $file = $data['excel_file'];
                        $path = $file->store('imports');

                        $import = new QuestionsImport($data['category_id']);
                        $jobId = $import->getJobId();

                        // Queue the import job
                        Excel::queueImport($import, Storage::disk('local')->path($path));

                        // Display immediate notification with job ID
                        Notification::make()
                            ->title(__('Questions import has been queued'))
                            ->body(__("Import job ID: {$jobId}. You will receive an email notification when the import is complete."))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Log::error("Failed to queue import job: " . $e->getMessage());
                        Notification::make()
                            ->title(__('Failed to queue questions import'))
                            ->body(__("An error occurred while trying to queue the import job. Please try again or contact support."))
                            ->danger()
                            ->send();
                    }
                })
        ];
    }

    protected static function excelColumnsMapping($headers)
    {
        $fields = [];
        foreach (['question', 'question_media_url', 'answer', 'answer_media_url',] as $column) {
            $fields[] = Forms\Components\Select::make("excel_column_mapping.$column")
                ->label(__($column))
                ->options(array_combine($headers, $headers))
                ->required();
        }
        foreach (['level', 'diff'] as $column) {
            $fields[] = Forms\Components\Select::make("excel_column_mapping.$column")
                ->label(__($column))
                ->options(array_combine($headers, $headers));
        }
        return $fields;
    }
    protected static function getExcelHeaders($file)
    {
        $path = $file->store('imports');
        $headers = Excel::toArray([], Storage::disk('local')->path($path))[0][0] ?? [];
        Storage::disk('local')->delete($path);

        return $headers;
    }
}
