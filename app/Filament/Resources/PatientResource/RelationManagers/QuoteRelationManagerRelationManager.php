<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Models\Quote;

class QuotesRelationManager extends RelationManager
{
    protected static string $relationship = 'quotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quote_number')
                    ->required()
                    ->maxLength(255),
                // Aquí puedes agregar campos para la cotización si quieres
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quote_number')
            ->columns([
                Tables\Columns\TextColumn::make('quote_number'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total_amount')->money('usd'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make("downloadPDF")
                    ->url(function( Quote $quote ){
                        return route("quote.generate.pdf", ["quote" => $quote]);
                    })
                    ->openUrlInNewTab()
                    ->label("PDF"),
                Action::make("generateHTML")
                    ->url(function( Quote $quote ){
                        return route("quote.generate.html", ["quote" => $quote]);
                    })
                    ->openUrlInNewTab()
                    ->label("Online"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
