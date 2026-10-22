<?php
// --- Файл: app/Filament/Resources/TeamResource/RelationManagers/NewsRelationManager.php (НОВЫЙ) ---

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NewsRelationManager extends RelationManager {
    protected static string $relationship = 'news';
    public function form(Form $form): Form { return $form->schema([Forms\Components\TextInput::make('title')->required(),]); }
    public function table(Table $table): Table { return $table->recordTitleAttribute('title')->columns([Tables\Columns\TextColumn::make('title'),])->headerActions([Tables\Actions\AttachAction::make(),])->actions([Tables\Actions\DetachAction::make(),]); }
}
