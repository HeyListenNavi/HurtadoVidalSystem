<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/generate-quote/pdf/{quote}", [InvoiceController::class, "generatePDF"])
    ->name("quote.generate.pdf");

Route::get("/generate-quote/html/{quote}", [InvoiceController::class, "generateHTML"])
    ->name("quote.generate.html");
