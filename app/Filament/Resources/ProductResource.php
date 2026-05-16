<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Text;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Product Details')->schema([
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->required(),
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp'),
                ])->columns(2),

                Section::make('Media')->schema([
                    FileUpload::make('images')
                        ->multiple()
                        ->reorderable()
                        ->disk('public')
                        ->panelLayout('grid')
                        ->maxFiles(4)
                        ->helperText('Start by uploading 4 images in this order: 1. Front View, 2. Packaging Detail, 3. Texture, 4. In Context.')
                        ->directory('products')
                        ->columnSpanFull(),
                ]),

                Section::make('Content')->schema([
                    RichEditor::make('description')
                        ->columnSpanFull(),
                ]),

                Section::make('Settings')->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_featured')
                            ->required(),
                        TextInput::make('discount_percent')
                            ->label('Discount Percent')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Masukkan persentase diskon (contoh: 10 untuk 10%).')
                            ->default(0),
                    ]),
                    Grid::make(2)->schema([
                        DateTimePicker::make('discount_starts_at')
                            ->label('Discount Starts At')
                            ->native(false)
                            ->seconds(false)
                            ->placeholder('Opsional'),
                        DateTimePicker::make('discount_ends_at')
                            ->label('Discount Ends At')
                            ->native(false)
                            ->seconds(false)
                            ->placeholder('Opsional'),
                    ]),
                    Placeholder::make('discount_note')
                        ->label('Preview')
                        ->content('Harga akhir akan dihitung otomatis dari harga dasar produk, persentase diskon, dan masa aktif diskon yang dipilih.'),
                ]),

                Section::make('Marketplace Links')->schema([
                    TextInput::make('shopee_link')
                        ->url()
                        ->placeholder('https://shopee.co.id/...'),
                    TextInput::make('tokopedia_link')
                        ->url()
                        ->placeholder('https://tokopedia.com/...'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->circular()
                    ->stacked()
                    ->limit(3),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->sortable(),
                TextColumn::make('price')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('discount_percent')
                    ->label('Diskon')
                    ->suffix('%')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price_after_discount')
                    ->label('Harga Setelah Diskon')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')
                    ->boolean(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
