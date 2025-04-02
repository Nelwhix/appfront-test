<?php

declare(strict_types=1);

use App\Jobs\SendPriceChangeNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
   $this->seed();
});

describe('AdminProductController@store', function (): void {
    it('creates a new product with default image', function (): void {
        $user = User::where('email', 'test@example.com')->first();
        $formRequest = [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'price' => fake()->numberBetween(100, 1000),
        ];

        $this->actingAs($user)->post(route('admin.products.store'), $formRequest)
            ->assertRedirectToRoute('admin.products.index');

        $this->assertDatabaseHas('products', [
            ...$formRequest,
            'image' => 'product-placeholder.jpg',
        ]);
    });

    it('creates a new product with user provided image', function (): void {
        Storage::fake('uploads');
        $user = User::where('email', 'test@example.com')->first();
        $file = UploadedFile::fake()->image('product.jpg');
        $formRequest = [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'price' => fake()->numberBetween(100, 1000),
            'image' => $file,
        ];

        $this->actingAs($user)->post(route('admin.products.store'), $formRequest)
            ->assertRedirectToRoute('admin.products.index');

        unset($formRequest['image']);
        $this->assertDatabaseHas('products', [
            ...$formRequest,
        ]);
        Storage::disk('uploads')->assertExists('uploads/' . $file->hashName());
    });
});

describe('AdminProductController@update', function (): void {
    it('updates a product', function (): void {
        Queue::fake();
        $user = User::where('email', 'test@example.com')->first();
        $product = Product::factory()->create();
        $newDescription = fake()->text();

        $route = route('admin.products.update', $product->id);
        $this->actingAs($user)->put($route, [
            'name' => $product->name,
            'description' => $newDescription,
            'price' => $product->price,
        ])->assertRedirectToRoute('admin.products.index');

        expect($product->refresh()->description)->toBe($newDescription);
        Queue::assertNotPushed(SendPriceChangeNotification::class);
    });

    it('dispatches price change notification if price is changed', function (): void {
        Queue::fake();
        $user = User::where('email', 'test@example.com')->first();
        $product = Product::factory()->create();

        $route = route('admin.products.update', $product->id);
        $this->actingAs($user)->put($route, [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price + 100,
        ])->assertRedirectToRoute('admin.products.index');
        Queue::assertPushed(SendPriceChangeNotification::class);
    });
});
