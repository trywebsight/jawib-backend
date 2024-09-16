<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components;
use App\Models\Setting;
use Filament\Notifications\Notification;

class SettingsManager extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.settings-manager';
    protected static ?string $slug = 'settings';
    protected static ?int $navigationSort = 50;

    public static function getNavigationLabel(): string
    {
        return __('settings');
    }
    public $data = [];

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function mount()
    {
        // Load existing settings into the form
        $settings = Setting::pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    protected function getFormSchema(): array
    {
        return [
            Components\Tabs::make('Settings')
                ->tabs([
                    Components\Tabs\Tab::make('General')
                        ->schema([
                            Components\FileUpload::make('site_logo')
                                ->label('Logo')
                                ->image()
                                ->directory('settings')
                                ->disk('public'),
                            Components\FileUpload::make('site_favicon')
                                ->label('Favicon')
                                ->image()
                                ->directory('settings')
                                ->disk('public'),
                        ])->columns(2),
                    Components\Tabs\Tab::make('Tap Payment')
                        ->schema([
                            Components\Toggle::make('test_mode')
                                ->label('Test Mode'),
                            Components\TextInput::make('secret_key')
                                ->label('Secret Key')
                                ->columnSpanFull(),
                        ])->columns(2),
                    Components\Tabs\Tab::make('Game Settings')
                        ->schema([
                            Components\FileUpload::make('win_sound_effect')
                                ->label('Win Sound Effect')
                                ->directory('settings')
                                ->disk('public'),
                            Components\FileUpload::make('lose_sound_effect')
                                ->label('Lose Sound Effect')
                                ->directory('settings')
                                ->disk('public'),
                        ])->columns(2),
                ]),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title(__('success'))
            ->body(__("settings updated successfully"))
            ->success()
            ->send();
    }
}
