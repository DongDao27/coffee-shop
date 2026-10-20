<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_replacement_and_validation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aZ1cAAAAASUVORK5CYII=');
        $this->get('/product-images')->assertOk();
        $this->put('/product-images/'.$product->id, ['image' => UploadedFile::fake()->createWithContent('coffee.png', $png)])->assertSessionHasNoErrors();
        $oldPath = $product->fresh()->image_path;
        Storage::disk('public')->assertExists($oldPath);
        $this->put('/product-images/'.$product->id, ['image' => UploadedFile::fake()->createWithContent('coffee.png', $png)])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($oldPath);
        $currentPath = $product->fresh()->image_path;
        Storage::disk('public')->assertExists($currentPath);
        $this->put('/product-images/'.$product->id, ['image' => UploadedFile::fake()->create('malware.txt', 1)])->assertSessionHasErrors('image');
        $this->put('/product-images/'.$product->id, ['image' => UploadedFile::fake()->createWithContent('large.png', $png.str_repeat('x', 2100000))])->assertSessionHasErrors('image');
        $this->assertSame($currentPath, $product->fresh()->image_path);
    }

    public function test_guests_cannot_upload(): void
    {
        $product = Product::factory()->create();
        $this->put('/product-images/'.$product->id)->assertRedirect('/login');
    }
}
