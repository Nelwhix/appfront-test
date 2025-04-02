<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendPriceChangeNotification;
use Illuminate\Support\Facades\Log;

class UpdateProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:update {id} {--name=} {--description=} {--price=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a product with the specified details';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Throwable
     */
    public function handle(): int
    {
        $id = $this->argument('id');
        $product = Product::find($id);
        if ($product === null) {
            $this->fail('Product not found.');
        }

        $data = [];
        if ($this->option('name')) {
            $data['name'] = $this->option('name');
            if (empty($data['name']) || trim($data['name']) == '') {
                $this->fail("Name cannot be empty.");
            }

            if (strlen($data['name']) < 3) {
                $this->fail("Name must be at least 3 characters long.");
            }
        }
        if ($this->option('description')) {
            $data['description'] = $this->option('description');
        }
        if ($this->option('price')) {
            $data['price'] = $this->option('price');
        }

        $oldPrice = $product->price;

        if (!empty($data)) {
            $product->update($data);

            $this->info("Product updated successfully.");

            // Check if price has changed
            if (isset($data['price']) && $oldPrice != $product->price) {
                $this->info("Price changed from {$oldPrice} to {$product->price}.");
                $notificationEmail = config('mail.price_notification_email');

                SendPriceChangeNotification::dispatch(
                    $product,
                    $oldPrice,
                    $product->price,
                    $notificationEmail
                );
                $this->info("Price change notification dispatched to {$notificationEmail}.");
            }
        } else {
            $this->info("No changes provided. Product remains unchanged.");
        }

        return 0;
    }
}
