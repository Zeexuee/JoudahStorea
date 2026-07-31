<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributorResource\Pages;
use App\Models\Distributor;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DistributorResource extends Resource
{
    protected static ?string $model = Distributor::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Distributor')->schema([
                TextInput::make('name')
                    ->label('Nama Toko/Mitra')
                    ->required(),
                TextInput::make('phone')
                    ->label('Nomor HP/WhatsApp')
                    ->tel()
                    ->required()
                    ->helperText('Format: 628xxxxxxxx (Gunakan kode negara tanpa tanda +)'),
                TextInput::make('city')
                    ->label('Kota')
                    ->required(),
                TextInput::make('province')
                    ->label('Provinsi')
                    ->required(),
            ])->columns(2),

            Section::make('Lokasi & Koordinat')->schema([
                Textarea::make('address')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('lat')
                    ->label('Latitude')
                    ->numeric()
                    ->required()
                    ->placeholder('-6.2626'),
                TextInput::make('lng')
                    ->label('Longitude')
                    ->numeric()
                    ->required()
                    ->placeholder('106.8669'),
                Toggle::make('featured')
                    ->label('Tampilkan sebagai Mitra Utama (Featured)')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telepon'),
                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDistributors::route('/'),
            'create' => Pages\CreateDistributor::route('/create'),
            'edit' => Pages\EditDistributor::route('/{record}/edit'),
        ];
    }
}
