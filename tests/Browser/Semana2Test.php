<?php

namespace Tests\Browser;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Semana2Test extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_user_not_logged_can_see_the_login_link()
    {
        $category = Category::factory()->create();

        $this->browse(function (Browser $browser) use ($category) {
            $browser->visit('/')
                    ->click('@perfil')
                    ->assertSeeLink('Iniciar sesión')
                    ->assertSeeLink('Registrarse')
                    ->screenshot('notLogged-test');
        });
    }

    /** @test */
    public function a_logged_user_can_see_the_logout_link()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($category, $user) {
            $browser->loginAs(User::find(1))->visit('/')
                ->click('@perfil')
                ->assertSeeLink('Perfil')
                ->assertSeeLink('Finalizar sesión')
                ->screenshot('logged-test');
        });
    }

    /** @test */
    public function it_can_shows_at_least_5_products_at_the_home_page()
    {
        $category = Category::factory()->create();

        $product1 = $this->createProduct($category);
        $product2 = $this->createProduct($category);
        $product3 = $this->createProduct($category);
        $product4 = $this->createProduct($category);
        $product5 = $this->createProduct($category);

        $this->browse(function (Browser $browser) use ($product1, $product2, $product3, $product4, $product5) {
            $browser->visit('/')
                ->assertSee($product1->name)
                ->pause(500)
                ->assertSee($product2->name)
                ->pause(500)
                ->assertSee($product3->name)
                ->pause(500)
                ->assertSee($product4->name)
                ->pause(500)
                ->assertSee($product5->name)
                ->screenshot('showProducts-test');
        });
    }

    /** @test */
    public function it_can_shows_at_least_5_published_products_at_the_home_page()
    {
        $category = Category::factory()->create();

        $product1 = $this->createProduct($category);
        $product2 = $this->createProduct($category, Product::BORRADOR);

        $this->browse(function (Browser $browser) use ($product1, $product2) {
            $browser->visit('/')
                ->assertSee($product1->name)
                ->pause(500)
                ->assertDontSee($product2->name)
                ->screenshot('showProductsPublished-test');
        });
    }

    /** @test */
    public function it_shows_the_details_page_of_a_category()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class,
        ]);

        $this->browse(function (Browser $browser) use ($product, $subcategory, $brand) {
            $browser->visit('/')
                ->click('@showMore')
                ->assertSee('Subcategorías')
                ->assertSeeLink($subcategory->name)
                ->assertSee('Marcas')
                ->assertSeeLink($brand->name)
                ->assertSee($product->name)
                ->screenshot('showDetailCategory-test');
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
