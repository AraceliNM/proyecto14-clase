<?php

namespace Tests\Browser;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Semana3Test extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function add_product_without_color_or_size_in_shopping_cart()
    {
        $category = Category::factory()->create();
        $product1 = $this->createProduct($category);

        $this->browse(function (Browser $browser) use ($product1) {
            $browser->visit('/products/' . $product1->slug)
                ->assertPresent('@addItemButton')
                ->click('@addItemButton')
                ->pause(500)
                ->click('@shoppingCart')
                ->screenshot('AddProduct-test');
        });
    }

    /** @test */
    public function add_product_with_color_in_shopping_cart()
    {
        $category = Category::factory()->create();
        $product1 = $this->createProduct($category, Product::PUBLICADO, true);

        $this->browse(function (Browser $browser) use ($product1) {
            $browser->visit('/products/' . $product1->slug)
                ->assertNotPresent('@addItemButton')
                ->click('@colorSelect')
                ->screenshot('AddProductWithColor-test');
        });
    }

    public function createProduct($category, $status = Product::PUBLICADO, $color = false, $size = false) {
        $brand = Brand::factory()->create();

        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => $color,
            'size' => $size
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
            'status' => $status
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class,
        ]);

        return $product;
    }
}
