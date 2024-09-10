<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Models\Package;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-m-archive-box';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('packages');
    }

    public static function getModelLabel(): string
    {
        return __('package');
    }

    public static function getPluralLabel(): string
    {
        return __('packages');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label(__('title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('games_count')->label(__('games count'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')->label(__('price'))
                    ->required()
                    ->numeric()
                    ->prefix(__('kwd')),
                Forms\Components\FileUpload::make('image')->label(__('image'))
                    ->directory('packages')
                    ->image()
                    ->disk('do')
                    ->visibility('public'),
                Forms\Components\Textarea::make('content')->label(__('description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label(__('title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('games_count')->label(__('games_count'))
                    ->numeric()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')->label(__('price'))
                    ->money('KWD')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')->label(__('image'))
                    ->disk('do'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            // 'view' => Pages\ViewPackage::route('/{record}'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
