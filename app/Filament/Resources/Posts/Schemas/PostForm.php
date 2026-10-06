<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use App\Models\Tag;
use App\Support\TagColors;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label('File')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'video/mp4'])
                    ->disk('public')
                    ->visibility('public')
                    ->directory('posts')
                    ->required()
                    ->columnSpanFull(),

                 TextInput::make('uploader_name')
                    ->label('Uploader')
                    ->formatStateUsing(fn (?Post $record): string => $record?->uploader?->name ?? '-')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),

                Select::make('rating')
                    ->options([
                        'general' => 'General',
                        'sensitive' => 'Sensitive',
                        'questionable' => 'Questionable',
                        'explicit' => 'Explicit',
                    ])
                    ->required()
                    ->default('general'),

                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->allowHtml()
                    ->getOptionLabelFromRecordUsing(fn (Tag $record): HtmlString => new HtmlString(
                        '<span style="color: '.TagColors::hex($record->category).'">'.e($record->name).'</span>'
                    ))
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        Select::make('category')
                            ->options([
                                'general' => 'General',
                                'artist' => 'Artist',
                                'character' => 'Character',
                                'copyright' => 'Copyright',
                                'meta' => 'Meta',
                            ])
                            ->default('general')
                            ->required(),
                    ]),

                TextInput::make('parent_id')
                    ->label('Parent post ID')
                    ->numeric()
                    ->minValue(1)
                    ->exists('posts', 'id')
                    ->rule(fn (?Post $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                        if (! $record || blank($value)) {
                            return;
                        }

                        if ($error = $record->parentAssignmentError((int) $value)) {
                            $fail($error);
                        }
                    })
                     ->helperText('Optional. Makes this post a child of the given post.'),

                TextInput::make('source')
                    ->url()
                    ->maxLength(255),

                Textarea::make('description')
                    ->columnSpanFull(),

                Checkbox::make('is_approved')
                    ->label('Approved')
                    ->default(false),
            ]);
    }
}