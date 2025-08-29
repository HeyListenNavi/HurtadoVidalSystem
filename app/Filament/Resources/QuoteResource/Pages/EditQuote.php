<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Sobreescribimos la lógica de guardado para la edición.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $productsData = $data['products'] ?? [];
        unset($data['products']);
        
        // Actualizamos los datos principales de la cotización
        $record->update($data);

        // Preparamos los datos para la tabla pivote
        $syncData = collect($productsData)->mapWithKeys(function ($item, $key) {
            return [$item['product_id'] => [
                'price'    => $item['price'],
                'quantity' => $item['quantity'],
                'order'    => $key + 1, // Usamos el índice del array para el orden
            ]];
        })->toArray();
            
        // Sincronizamos la relación. Esto eliminará las relaciones antiguas
        // y las reemplazará con las nuevas del formulario.
        $record->products()->sync($syncData);

        return $record;
    }
}