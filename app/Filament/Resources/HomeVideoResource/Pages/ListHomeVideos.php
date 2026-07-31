<?php

namespace App\Filament\Resources\HomeVideoResource\Pages;

use App\Filament\Resources\HomeVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeVideos extends ListRecords
{
    protected static string $resource = HomeVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
