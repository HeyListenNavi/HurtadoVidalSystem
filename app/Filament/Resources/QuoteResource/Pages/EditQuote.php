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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Usar el nuevo nombre
        $productsData = $data['quote_products_data'] ?? [];
        unset($data['quote_products_data']);

        $record->update($data);

        $syncData = collect($productsData)->mapWithKeys(function ($item, $key) {
            return [
                $item['product_id'] => [
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'],
                    'order'    => $key + 1,
                ],
            ];
        })->toArray();

        $record->products()->sync($syncData);

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Llenar el Repeater usando el nuevo nombre
        $data['quote_products_data'] = $this->record
            ->products()
            ->withPivot(['quantity', 'price', 'order'])
            ->orderByPivot('order')
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'quantity'   => $product->pivot->quantity,
                    'price'      => $product->pivot->price,
                ];
            })
            ->values()
            ->toArray();

        return $data;
    }

}