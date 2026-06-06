<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeVideoResource\Pages;
use App\Models\HomeVideo;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeVideoResource extends Resource
{
    protected static ?string $model = HomeVideo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationLabel = 'Home Videos';
    protected static ?string $modelLabel = 'Home Video';
    protected static ?string $pluralModelLabel = 'Home Videos';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Video')->schema([
                TextInput::make('title')
                    ->label('Judul Video')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi / Caption')
                    ->rows(3),
            ])->columns(1),

            Section::make('Sumber Video & Cover')->schema([
                FileUpload::make('video_path')
                    ->label('Unggah File Video (MP4)')
                    ->disk('public')
                    ->directory('videos')
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(20480) // 20MB
                    ->helperText('Unggah video dalam format MP4 (Rasio 9:16 vertikal direkomendasikan). Maksimal 20MB.'),
                TextInput::make('external_video_url')
                    ->label('Atau Link Video Eksternal (Direct MP4)')
                    ->url()
                    ->helperText('Gunakan jika Anda ingin menempelkan link video MP4 langsung (misal dari CDN/Mixkit). Jika mengunggah file di atas, kosongkan kolom ini.'),
                FileUpload::make('thumbnail_path')
                    ->label('Cover / Thumbnail Video')
                    ->image()
                    ->disk('public')
                    ->directory('videos/thumbnails')
                    ->helperText('Cover gambar yang tampil sebelum video diputar.'),
            ])->columns(1),

            Section::make('Navigasi & Pengaturan')->schema([
                TextInput::make('social_media_url')
                    ->label('Link Media Sosial / Order')
                    ->url()
                    ->required()
                    ->placeholder('https://instagram.com/p/...'),
                Select::make('social_media_platform')
                    ->label('Platform Media Sosial')
                    ->options([
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'youtube' => 'YouTube',
                        'whatsapp' => 'WhatsApp',
                        'shopee' => 'Shopee',
                        'other' => 'Lainnya',
                    ])
                    ->required()
                    ->default('instagram'),
                Toggle::make('is_active')
                    ->label('Aktif / Tampilkan')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('Thumbnail')
                    ->disk('public'),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('social_media_platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'instagram' => 'danger',
                        'tiktok' => 'gray',
                        'youtube' => 'danger',
                        'whatsapp' => 'success',
                        'shopee' => 'warning',
                        default => 'primary',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
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
            'index' => Pages\ListHomeVideos::route('/'),
            'create' => Pages\CreateHomeVideo::route('/create'),
            'edit' => Pages\EditHomeVideo::route('/{record}/edit'),
        ];
    }
}
