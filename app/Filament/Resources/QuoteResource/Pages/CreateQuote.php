<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Usar el nuevo nombre del Repeater
        $productsData = $data['quote_products_data'] ?? [];
        unset($data['quote_products_data']);

        $quote = static::getModel()::create($data);

        $syncData = collect($productsData)->mapWithKeys(function ($item, $key) {
            return [
                $item['product_id'] => [
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'],
                    'order'    => $key + 1,
                ],
            ];
        })->toArray();

        $quote->products()->sync($syncData);

        return $quote;
    }
}