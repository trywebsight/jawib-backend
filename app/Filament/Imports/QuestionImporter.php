<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\Question;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Filament\Forms;

class QuestionImporter extends Importer
{
    protected static ?string $model = Question::class;

    public ?int $category_id = null;

    public static function getOptionsFormComponents(): array
    {
        return [
            Forms\Components\Select::make('category_id')
                ->label('Category')
                ->options(Category::pluck('title', 'id'))
                ->required(),
            // Checkbox::make('updateExisting')
            // ->label('Update existing records'),
        ];
    }

    public static function getColumns(): array
    {
        return [
            // Question
            ImportColumn::make('question')
                ->requiredMapping()
                ->guess(['question', 'Q name'])
                ->exampleHeader('question')
                ->rules(['required', 'string', 'max:255']),
            // Answer
            ImportColumn::make('answer')
                ->requiredMapping()
                ->guess(['answer', 'Q answer'])
                ->exampleHeader('answer')
                ->rules(['required', 'string', 'max:255']),
            // Question Media
            // ImportColumn::make('question_media_url')
            //     ->requiredMapping()
            //     ->guess(['question_media_url', 'Q media'])
            //     ->exampleHeader('question_media_url')
            //     ->rules(['nullable', 'sometimes', 'string', 'max:255']),
            // Answer Media
            // ImportColumn::make('answer_media_url')
            //     ->requiredMapping()
            //     ->rules(['required', 'string', 'max:255'])
            //     ->guess(['answer_media_url', 'Answer media'])
            //     ->exampleHeader('answer_media_url')
            //     ->rules(['nullable', 'sometimes', 'string', 'max:255']),
            // Question Difficulty level
            ImportColumn::make('level')
                ->requiredMapping()
                ->guess(['level'])
                ->exampleHeader('Level')
                ->rules(['nullable', 'sometimes', 'numeric', 'min:1', 'max:3']),
            // Year differance
            ImportColumn::make('diff')
                ->requiredMapping()
                ->guess(['diff'])
                ->rules(['nullable', 'sometimes', 'numeric']),

        ];
    }

    public function resolveRecord(): ?Question
    {
        return Question::firstOrNew([
            'question' => $this->data['question'],
            'category_id' => 5,
            // 'category_id' => $this->options['category_id'],
        ]);
    }

    // protected function beforeSave(Import $import): void
    // {
    //     // dd($this->options['category_id']);
    //     // if (!empty($this->data['question_media_'])) {
    //     //     $this->record->question_media = $this->downloadAndStoreImage($this->data['cover_image']);
    //     // }
    // }

    protected function downloadAndStoreImage($url): string
    {
        if (empty($url)) {
            return '';
        }

        $imageArray = explode(',', $url);
        // Return the first image
        $imgLink =  $imageArray[0];

        // Check if $imgLink is a URL
        if (!filter_var($imgLink, FILTER_VALIDATE_URL)) {
            // If it's not a URL, assume it's a local path and return it as is
            return $imgLink;
        }

        $response = Http::get($imgLink);

        if ($response->successful()) {
            $filename = 'questions/' . Str::random(30) . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            Storage::disk('public')->put($filename, $response->body());
            return $filename;
        }

        return '';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your question import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMinutes(1);
    }
}
