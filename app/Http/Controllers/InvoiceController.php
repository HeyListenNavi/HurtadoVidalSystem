<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Support\Facades\App;

class InvoiceController extends Controller
{
    /**
     * Genera y retorna la factura en formato PDF.
     */
    public function generatePDF(Quote $quote)
    {
        // Define el vendedor (tu empresa/clínica)
        $seller = new Party([
            'name'          => 'Tu Empresa de Salud',
            'phone'         => '123-456-7890',
            'custom_fields' => [
                'email' => 'contacto@tuempresa.com',
                'website' => 'www.tuempresa.com',
            ],
        ]);

        // Define el comprador (el paciente)
        $customer = new Party([
            'name'          => $quote->patient->first_name . ' ' . $quote->patient->last_name,
            'address'       => $quote->patient->address,
            'custom_fields' => [
                'email' => $quote->patient->email,
                'quote number' => $quote->quote_number,
            ],
        ]);

        // Mapea los productos de la cotización a objetos InvoiceItem
        $items = $quote->products->map(function ($product) {
            return InvoiceItem::make($product->name)
                ->description($product->description)
                ->pricePerUnit($product->pivot->price)
                ->quantity($product->pivot->quantity);
        })->toArray();

        // Crea la factura con los datos de la cotización
        $invoice = Invoice::make()
            ->series('COT')
            ->status('unpaid')
            ->sequence($quote->id) // Puedes usar el ID de la cotización
            ->serialNumberFormat('{SEQUENCE}-{SERIES}')
            ->seller($seller)
            ->buyer($customer)
            ->date($quote->created_at)
            ->dateFormat('d/m/Y')
            ->currencySymbol('$')
            ->currencyCode('USD')
            ->addItems($items)
            ->notes($quote->status)
            ->filename('Cotización-' . $quote->quote_number);

        // Retorna la factura como un stream PDF para visualizarla en el navegador
        return $invoice->stream();
    }
    
    /**
     * Genera y retorna la factura como una vista HTML.
     */
    public function generateHTML(Quote $quote)
    {
        // Vendedor y comprador
        $sellerName = 'Tu Empresa de Salud';
        $sellerPhone = '123-456-7890';
        $sellerEmail = 'contacto@tuempresa.com';
        $sellerWebsite = 'www.tuempresa.com';

        $customerName = $quote->patient->first_name . ' ' . $quote->patient->last_name;
        $customerAddress = $quote->patient->address;
        $customerEmail = $quote->patient->email;
        $quoteNumber = $quote->quote_number;
        $quoteDate = $quote->created_at->format('d/m/Y');

        // Ítems de la cotización (asegurando valores no nulos)
        $items = $quote->products->map(function ($product) {
            return [
                'name' => $product->name ?? 'Sin nombre',
                'description' => $product->description ?? 'Sin descripción',
                'price' => $product->pivot->price ?? 0.00,
                'quantity' => $product->pivot->quantity ?? 1,
            ];
        });

        // HTML y Tailwind CSS
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Cotización {$quoteNumber}</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-100 p-8 font-sans'>
            <div class='bg-white p-6 rounded-lg shadow-xl mx-auto max-w-4xl'>
                <header class='flex justify-between items-center border-b pb-4 mb-6'>
                    <div>
                        <h1 class='text-3xl font-bold text-gray-800'>COTIZACIÓN</h1>
                        <p class='text-sm text-gray-500 mt-1'>Número: <span class='font-medium text-gray-700'>{$quoteNumber}</span></p>
                        <p class='text-sm text-gray-500 mt-1'>Fecha: <span class='font-medium text-gray-700'>{$quoteDate}</span></p>
                    </div>
                    <div class='text-right'>
                        <h2 class='text-xl font-semibold text-gray-800'>{$sellerName}</h2>
                        <p class='text-sm text-gray-600'>{$sellerPhone}</p>
                        <p class='text-sm text-gray-600'>{$sellerEmail}</p>
                        <p class='text-sm text-gray-600'>{$sellerWebsite}</p>
                    </div>
                </header>

                <section class='mb-6'>
                    <h3 class='text-lg font-semibold text-gray-800 mb-2'>Cliente</h3>
                    <p class='text-gray-700'><strong>Nombre:</strong> {$customerName}</p>
                    <p class='text-gray-700'><strong>Dirección:</strong> {$customerAddress}</p>
                    <p class='text-gray-700'><strong>Email:</strong> {$customerEmail}</p>
                </section>

                <section class='mb-6'>
                    <table class='min-w-full bg-white'>
                        <thead>
                            <tr class='bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                <th class='py-3 px-6'>Producto</th>
                                <th class='py-3 px-6 text-right'>Precio Unitario</th>
                                <th class='py-3 px-6 text-right'>Cantidad</th>
                                <th class='py-3 px-6 text-right'>Total</th>
                            </tr>
                        </thead>
                        <tbody class='divide-y divide-gray-200'>";

        $totalAmount = 0;
        foreach ($items as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $totalAmount += $itemTotal;
            $html .= "
                            <tr>
                                <td class='py-4 px-6 text-sm font-medium text-gray-900'>{$item['name']}</td>
                                <td class='py-4 px-6 text-sm text-gray-500 text-right'>\${$item['price']}</td>
                                <td class='py-4 px-6 text-sm text-gray-500 text-right'>{$item['quantity']}</td>
                                <td class='py-4 px-6 text-sm font-semibold text-gray-900 text-right'>\${$itemTotal}</td>
                            </tr>";
        }

        $html .= "
                        </tbody>
                    </table>
                </section>

                <section class='flex justify-end mt-8'>
                    <div class='w-1/2'>
                        <div class='flex justify-between font-bold text-gray-800'>
                            <p>Total:</p>
                            <p>\${$totalAmount}</p>
                        </div>
                    </div>
                </section>

                <footer class='text-center text-gray-500 text-xs mt-12'>
                    <p>Gracias por tu interés. Si tienes alguna pregunta, no dudes en contactarnos.</p>
                </footer>
            </div>
        </body>
        </html>
        ";

        return response($html);
    }
}