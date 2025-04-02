<?php

declare(strict_types=1);

use App\Jobs\SendPriceChangeNotification;
use App\Models\Product;

describe('UpdateProductCommand', function (): void {
    it('fails if product does not exist', function (): void {
        $this->artisan('product:update', ['id' => 1])
            ->expectsOutputToContain('Product not found')
            ->assertFailed();
    });

    it('does nothing if no changes provided', function (): void {
        $product = Product::factory()->create();

        $this->artisan('product:update', ['id' => $product->id])
            ->expectsOutputToContain('No changes provided. Product remains unchanged.')
            ->assertExitCode(0);
    });

    it('updates a product', function (): void {
        Queue::fake();
        $product = Product::factory()->create();
        $parameters = [
            'id' => $product->id,
            '--name' => 'Test Product',
            '--description' => 'Test Description',
        ];

        $this->artisan('product:update', $parameters)
            ->expectsOutputToContain('Product updated successfully.')
            ->assertSuccessful();

        expect($product->refresh()->name)->toBe('Test Product')
            ->and($product->description)->toBe('Test Description');

        Queue::assertNotPushed(SendPriceChangeNotification::class);
    });

    it('dispatches notification if price changes', function (): void {
        Queue::fake();
        $product = Product::factory()->create();
        $parameters = [
            'id' => $product->id,
            '--name' => 'Test Product',
            '--description' => 'Test Description',
            '--price' => $product->price + 600,
        ];

        $this->artisan('product:update', $parameters)
            ->expectsOutputToContain('Price change notification dispatched')
            ->assertSuccessful();

        Queue::assertPushed(SendPriceChangeNotification::class);
    });
});

