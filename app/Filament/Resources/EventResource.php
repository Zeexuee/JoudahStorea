<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Berita & Event';
    protected static ?string $modelLabel = 'Event';
    protected static ?string $pluralModelLabel = 'Events';
    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen Konten';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Event')->schema([
                    TextInput::make('title')
                        ->label('Judul Event')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                        
                    DatePicker::make('event_date')
                        ->label('Tanggal Event'),
                        
                    TextInput::make('external_url')
                        ->label('Link Eksternal Event (Opsional)')
                        ->url()
                        ->maxLength(255)
                        ->helperText('Isi jika Anda ingin pengunjung langsung diarahkan ke halaman eksternal (misal: link pendaftaran atau webinar) saat mengklik event ini.'),
                        
                    RichEditor::make('description')
                        ->label('Deskripsi')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Media & Status')->schema([
                    FileUpload::make('image')
                        ->label('Gambar Banner Utama')
                        ->image()
                        ->disk('public')
                        ->directory('events'),
                        
                    FileUpload::make('detail_image')
                        ->label('Gambar Detail Event (Opsional)')
                        ->image()
                        ->disk('public')
                        ->directory('events/details')
                        ->helperText('Gambar tambahan yang akan ditampilkan di dalam halaman detail acara.'),
                        
                    Toggle::make('is_active')
                        ->label('Aktif / Tampilkan')
                        ->default(true),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Banner')
                    ->disk('public'),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Tanggal Event')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
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
                \Filament\Actions\DeleteAction::make(),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
