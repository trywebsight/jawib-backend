<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Imports\QuestionImporter;
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
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Log;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(QuestionImporter::class)
                ->chunkSize(100)
                ->csvDelimiter(',')
                ->fileRules([
                    'max:2048',
                    File::types(['csv', 'xlsx'])->max(2048),
                ]),
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
