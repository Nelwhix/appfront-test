<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->paginate(20);
        $exchangeRate = $this->getExchangeRate();

        return view('products.list', compact('products', 'exchangeRate'));
    }

    public function show(string $product_id)
    {
        $product = Product::findOrFail($product_id);
        $exchangeRate = $this->getExchangeRate();

        return view('products.show', compact('product', 'exchangeRate'));
    }

    /**
     * @return float
     */
    private function getExchangeRate()
    {
        return Cache::remember('exchange_rate', now()->addMinutes(60), function () {
            try {
                $response = Http::timeout(5)->throw()->get('https://open.er-api.com/v6/latest/USD')->json();
                if (isset($response['rates']['EUR'])) {
                    return $response['rates']['EUR'];
                }
            } catch (Exception $e) {
                Log::error('Call to exchange rate API failed with message: ' . $e->getMessage());
            }

            return config('app.exchange_rate');
        });
    }

}
