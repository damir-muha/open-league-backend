<?php

namespace App\Filament\Resources\GameResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;

class GameMediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('media_type')
                    ->label('Type media')
                    ->options([
                        'photo' => 'Photo',
                        'youtube_video' => 'Video YouTube',
                    ])
                    ->required()
                    ->reactive(),
                TextInput::make('title')
                    ->label('Title (optional)')
                    ->maxLength(255),
                FileUpload::make('photo_upload')
                    ->label('Photo upload')
                    ->disk('public')
                    ->directory('game-photos')
                    ->image()
                    ->visible(fn (callable $get) => $get('media_type') === 'photo'),
                TextInput::make('video_url')
                    ->label('URL video YouTube')
                    ->url()
                    ->visible(fn (callable $get) => $get('media_type') === 'youtube_video'),
            ]);
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($data['media_type'] === 'photo') {
            $data['photo_upload'] = $data['url'];
        } elseif ($data['media_type'] === 'youtube_video') {
            $data['video_url'] = $data['url'];
        }

        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('media_type')->label('Type'),
                ImageColumn::make('url')
                    ->label('Photo')
                    ->disk('public')
                    ->visible(fn ($record) => $record && $record->media_type === 'photo'),
                TextColumn::make('url')
                    ->label('URL for video')
                    ->visible(fn ($record) => $record && $record->media_type === 'youtube_video'),
                TextColumn::make('title')->label('Title'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): Model {
                        if ($data['media_type'] === 'photo') {
                            $data['url'] = $data['photo_upload'] ?? null;
                        } elseif ($data['media_type'] === 'youtube_video') {
                            $data['url'] = $data['video_url'] ?? null;
                        }
                        unset($data['photo_upload'], $data['video_url']);
                        return $livewire->getRelationship()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $updateData = [
                            'title' => $data['title'],
                            'media_type' => $data['media_type'],
                        ];

                        if ($data['media_type'] === 'photo') {
                            if (!empty($data['photo_upload'])) {
                                $updateData['url'] = $data['photo_upload'];
                            }
                        } elseif ($data['media_type'] === 'youtube_video') {
                            $updateData['url'] = $data['video_url'];
                        }

                        $record->update($updateData);
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
