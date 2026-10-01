<?php

namespace App\Filament\Resources\DeletionReports\Tables;

use App\Models\Comment;
use App\Models\DeletionReport;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DeletionReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['reporter', 'reviewer']))
            ->columns([
                TextColumn::make('reportable_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->color(fn (string $state): string => $state === Post::class ? 'info' : 'gray'),
                ImageColumn::make('preview')
                    ->label('Preview')
                    ->disk('public')
                    ->square()
                    ->getStateUsing(function (DeletionReport $record): ?string {
                        $subject = $record->subject();

                        return $subject instanceof Post && ! $subject->thumbnailIsVideo()
                            ? $subject->thumbnail_path
                            : null;
                    }),
                TextColumn::make('subject_label')
                    ->label('Reported')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('reporter.name')
                    ->label('Reported by')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('reason')
                    ->wrap()
                    ->limit(100)
                    ->tooltip(fn (DeletionReport $record): string => $record->reason),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        DeletionReport::STATUS_APPROVED => 'danger',
                        DeletionReport::STATUS_REJECTED => 'success',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Reported at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('review_note')
                    ->label('Review note')
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        DeletionReport::STATUS_PENDING => 'Pending',
                        DeletionReport::STATUS_APPROVED => 'Approved (deleted)',
                        DeletionReport::STATUS_REJECTED => 'Rejected (restored)',
                    ])
                    ->default(DeletionReport::STATUS_PENDING),
                SelectFilter::make('reportable_type')
                    ->label('Type')
                    ->options([
                        Post::class => 'Post',
                        Comment::class => 'Comment',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('details')
                    ->label('Details')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->modalHeading(fn (DeletionReport $record): string => $record->subject_label)
                    ->modalContent(fn (DeletionReport $record) => view('filament.deletion-reports.details', [
                        'report' => $record,
                        'subject' => $record->subject(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('approve')
                    ->label('Approve deletion')
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->visible(fn (DeletionReport $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalDescription('The post or comment will be permanently deleted.')
                    ->schema([
                        Textarea::make('review_note')
                            ->label('Note (optional)')
                            ->maxLength(1000),
                    ])
                    ->action(function (DeletionReport $record, array $data): void {
                        $record->approve(auth()->user(), $data['review_note'] ?? null);

                        Notification::make()->title('Deleted')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject and restore')
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->color('success')
                    ->visible(fn (DeletionReport $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->modalDescription('The post or comment will be shown on the site again.')
                    ->schema([
                        Textarea::make('review_note')
                            ->label('Note (optional)')
                            ->maxLength(1000),
                    ])
                    ->action(function (DeletionReport $record, array $data): void {
                        $record->reject(auth()->user(), $data['review_note'] ?? null);

                        Notification::make()->title('Restored')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Approve deletion')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (DeletionReport $record) => $record->approve(auth()->user()));
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('rejectSelected')
                        ->label('Reject and restore')
                        ->icon(Heroicon::ArrowUturnLeft)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (DeletionReport $record) => $record->reject(auth()->user()));
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
