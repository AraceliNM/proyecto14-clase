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

    /** @test */
    public function filter_products_by_subcategory()
    {
        $category = Category::factory()->create();

        $product = $this->createProduct($category);
        $product2 = $this->createProduct($category);

        $this->browse(function (Browser $browser) use ($product, $product2, $category) {
            $browser->visit('/categories/' . $category->slug)
                ->assertSee('Subcategorías')
                ->click('@filterSubcategory')
                ->assertSee($product->name)
                ->assertDontSee($product2->name)
                ->screenshot('filterProductBySubcategory-test');
        });
    }

    /** @test */
    public function filter_products_by_brand()
    {
        $category = Category::factory()->create();

        $product = $this->createProduct($category);
        $product2 = $this->createProduct($category);

        $this->browse(function (Browser $browser) use ($product, $product2, $category) {
            $browser->visit('/categories/' . $category->slug)
                ->assertSee('Marcas')
                ->click('@filterBrand')
                ->assertSee($product->name)
                ->assertDontSee($product2->name)
                ->screenshot('filterProductByBrand-test');
        });
    }

    /** @test */
    public function it_shows_the_product_details_page()
    {
        $category = Category::factory()->create();
        $product = $this->createProduct($category);

        $this->browse(function (Browser $browser) use ($product, $category) {
            $browser->visit('/categories/' . $category->slug)
                ->clickLink($product->name)
                ->assertSee($product->name)
                ->assertSee($product->description)
                ->pause(500)
                ->assertSee($product->price)
                ->assertSee($product->quantity)
                ->assertVisible('@imageProduct')
                ->pause(500)
                ->assertVisible('@decrementButton')
                ->assertVisible('@incrementButton')
                ->assertVisible('@addItemButton')
                ->screenshot('showDetailProduct-test');
        });
    }

    /** @test */
    public function the_limit_of_the_increment_button_is_the_max_quantity()
    {
        $category = Category::factory()->create();

        $brand = Brand::factory()->create();

        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => false,
            'size' => false
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'quantity' => 3,
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class,
        ]);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertButtonEnabled('@incrementButton')
                ->press('@incrementButton')
                ->pause(500)
                ->press('@incrementButton')
                ->pause(500)
                ->assertButtonDisabled('@incrementButton')
                ->screenshot('incrementButton-test');
        });
    }

    /** @test */
    public function the_limit_of_the_decrement_button_is_one()
    {
        $category = Category::factory()->create();

        $brand = Brand::factory()->create();

        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => false,
            'size' => false
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'quantity' => 3,
        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class,
        ]);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertButtonDisabled('@decrementButton')
                ->press('@incrementButton')
                ->pause(500)
                ->assertButtonEnabled('@decrementButton')
                ->screenshot('decrementButton-test');
        });
    }

    /** @test */
    public function show_the_select_menu_of_size_or_color_depending_its_subcategory()
    {
        $category1 = Category::factory()->create();
        $brand = Brand::factory()->create();
        $category1->brands()->attach($brand->id);

        $subcategory1 = Subcategory::factory()->create([
            'category_id' => $category1->id,
            'color' => false,
            'size' => false
        ]);

        $product1 = Product::factory()->create([
            'subcategory_id' => $subcategory1->id,
            'quantity' => 3,
        ]);

        Image::factory()->create([
            'imageable_id' => $product1->id,
            'imageable_type' => Product::class,
        ]);

        $category2 = Category::factory()->create();
        $category2->brands()->attach($brand->id);

        $subcategory2 = Subcategory::factory()->create([
            'category_id' => $category2->id,
            'color' => true,
            'size' => false
        ]);

        $product2 = Product::factory()->create([
            'subcategory_id' => $subcategory2->id,
            'quantity' => 3,
        ]);

        Image::factory()->create([
            'imageable_id' => $product2->id,
            'imageable_type' => Product::class,
        ]);

        $category3 = Category::factory()->create();
        $category3->brands()->attach($brand->id);

        $subcategory3 = Subcategory::factory()->create([
            'category_id' => $category3->id,
            'color' => true,
            'size' => true
        ]);

        $product3 = Product::factory()->create([
            'subcategory_id' => $subcategory3->id,
            'quantity' => 3,
        ]);

        Image::factory()->create([
            'imageable_id' => $product3->id,
            'imageable_type' => Product::class,
        ]);

        $this->browse(function (Browser $browser) use ($product1, $product2, $product3) {
            $browser->visit('/products/' . $product1->slug)
                ->pause(500)
                ->assertNotPresent('@colorSelect')
                ->assertNotPresent('@sizeSelect')
                ->screenshot('productWithoutColorAndSize-test');
            $browser->pause(500);
            $browser->visit('/products/' . $product2->slug)
                ->pause(500)
                ->assertPresent('@colorSelect')
                ->assertNotPresent('@sizeSelect')
                ->screenshot('productWithColor-test');
            $browser->pause(500);
            $browser->visit('/products/' . $product3->slug)
                ->pause(500)
                ->assertPresent('@colorSelect')
                ->assertPresent('@sizeSelect')
                ->screenshot('productWithColorAndSize-test');
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
