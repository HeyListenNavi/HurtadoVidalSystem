<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Models\Quote;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    /**
     * Override the default Filament record creation logic to
     * manually handle the `BelongsToMany` relationship with Repeater.
     * This avoids the "Field 'name' doesn't have a default value" error.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $productsData = $data['products'] ?? [];
        unset($data['products']);
        
        // Creamos la cotización
        $quote = static::getModel()::create($data);

        // Preparamos los datos para la tabla pivote
        $syncData = collect($productsData)->mapWithKeys(function ($item, $key) {
            // Asegúrate de que el order se guarda correctamente
            return [$item['product_id'] => [
                'price'    => $item['price'],
                'quantity' => $item['quantity'],
                'order'    => $key + 1, // Usamos el índice del array para el orden
            ]];
        })->toArray();
            
        // Sincronizamos la relación
        $quote->products()->sync($syncData);

        return $quote;
    }
}
