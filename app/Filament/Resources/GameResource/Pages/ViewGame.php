<?php

namespace App\Filament\Resources\GameResource\Pages;

use App\Filament\Resources\GameResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewGame extends ViewRecord
{
    protected static string $resource = GameResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $schema = [
            Infolists\Components\TextEntry::make('user.name')
                ->label(__('user')),
            Infolists\Components\TextEntry::make('title')
            ->label(__('game title')),
            Infolists\Components\TextEntry::make('categories.title')
                ->badge()
                ->columnSpanFull()
                ->label(__('categories')),
        ];

        $record = $this->record;

        // Group questions by category title
        $categories = $record->questions->groupBy('category.title');

        // Iterate over each category
        foreach ($categories as $categoryTitle => $questions) {
            // Group questions by level within the category
            $levelGroups = $questions->groupBy('level');

            $levelSchemas = [];

            // Iterate over each level within the category
            foreach ($levelGroups as $level => $levelQuestions) {
                $levelLabel = match ($level) {
                    1 => __('easy'),
                    2 => __('medium'),
                    3 => __('hard'),
                    default => 'Unknown',
                };

                // Define CSS classes for levels
                $levelColorClass = match ($level) {
                    1 => 'background: #00800047;',
                    2 => 'background: #ffa5003d;',
                    3 => 'background: #ff00004f;',
                    default => 'bg-gray-100',
                };

                // Build the schema for each level
                $levelSchemas[] = Infolists\Components\Section::make($levelLabel)
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema(
                                $levelQuestions->map(function ($question) {
                                    return Infolists\Components\TextEntry::make('question_' . $question->id)
                                        ->label("#{$question->id} | {$question->question}");
                                })->toArray()
                            ),
                    ])
                    ->collapsed(false)
                    ->extraAttributes(['style' => $levelColorClass]); // Apply color class
            }

            // Add the category section to the main schema
            $schema[] = Infolists\Components\Section::make($categoryTitle)
                ->schema($levelSchemas)
                ->columnSpan(1)
                ->collapsed(false)
                ->extraAttributes(['class' => 'text-center']); // Center the category title
        }

        return $infolist->schema($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
