<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Tag;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['file_path'];
        $fullPath = Storage::disk('public')->path($path);

        $data['file_name'] = basename($path);
        $data['file_ext'] = pathinfo($path, PATHINFO_EXTENSION);
        $data['file_size'] = Storage::disk('public')->size($path);
        $data['md5'] = md5_file($fullPath);
        
        if ($imageSize = @getimagesize($fullPath)) {
            $data['width'] = $imageSize[0];
            $data['height'] = $imageSize[1];
        }

        $data['thumbnail_path'] = $path;

        if (auth()->check()) {
            $data['uploader_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Tag::recalculateAllPostCounts();
    }
}
