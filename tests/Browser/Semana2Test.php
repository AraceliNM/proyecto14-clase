<?php

namespace Tests\Browser;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Semana2Test extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_user_not_logged_can_see_the_login_link()
    {
        $category = $this->createCategory();

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->click('@perfil')
                    ->assertSeeLink('Iniciar sesión')
                    ->assertSeeLink('Registrarse')
                    ->screenshot('notLogged');
        });
    }

    /** @test */
    public function a_logged_user_can_see_the_logout_link()
    {
        $category = $this->createCategory();
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))->visit('/')
                ->click('@perfil')
                ->assertSeeLink('Perfil')
                ->assertSeeLink('Finalizar sesión')
                ->screenshot('logged');
        });
    }

    /** @test */
    public function it_can_shows_at_least_5_products_at_the_home_page()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = [
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id)
        ];

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/')
                ->assertSee(Str::limit($product[0]->name, 20))
                ->assertSee(Str::limit($product[1]->name, 20))
                ->assertSee(Str::limit($product[2]->name, 20))
                ->assertSee(Str::limit($product[3]->name, 20))
                ->assertSee(Str::limit($product[4]->name, 20))
                ->screenshot('showFiveProducts');
        });
    }

    /** @test */
    public function it_can_shows_at_least_5_published_products_at_the_home_page()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = [
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id, Product::BORRADOR),
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id),
            $this->createProduct($subcategory->id, $brand->id)
        ];

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/')
                ->pause(500)
                ->assertSee(Str::limit($product[0]->name, 20))
                ->assertSee(Str::limit($product[1]->name, 20))
                ->pause(500)
                ->assertSee(Str::limit($product[3]->name, 20))
                ->assertSee(Str::limit($product[4]->name, 20))
                ->assertSee(Str::limit($product[5]->name, 20))
                ->pause(500)
                ->assertDontSee(Str::limit($product[2]->name, 20))
                ->screenshot('showFiveProductsPublished');
        });
    }

    /** @test */
    public function it_shows_the_details_page_of_a_category()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product, $category, $subcategory, $brand) {
            $browser->visit('/categories/' . $category->slug)
                ->assertSee('Subcategorías')
                ->assertSeeLink($subcategory->name)
                ->assertSee('Marcas')
                ->assertSeeLink($brand->name)
                ->assertSee($product->name)
                ->screenshot('showDetailCategory');
        });
    }

    /** @test */
    public function filter_products_by_subcategory()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $subcategory2 = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);
        $brand2 = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);
        $product2 = $this->createProduct($subcategory2->id, $brand2->id);

        $this->browse(function (Browser $browser) use ($product, $product2, $category) {
            $browser->visit('/categories/' . $category->slug)
                ->assertSee('Subcategorías')
                ->click('@filterSubcategory')
                ->assertSee(Str::limit($product->name, 20))
                ->pause(500)
                ->assertDontSee(Str::limit($product2->name, 20))
                ->screenshot('filterProductBySubcategory');
        });
    }

    /** @test */
    public function filter_products_by_brand()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $subcategory2 = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);
        $brand2 = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);
        $product2 = $this->createProduct($subcategory2->id, $brand2->id);

        $this->browse(function (Browser $browser) use ($product, $product2, $category) {
            $browser->visit('/categories/' . $category->slug)
                ->assertSee('Marcas')
                ->click('@filterBrand')
                ->assertSee(Str::limit($product->name, 20))
                ->pause(500)
                ->assertDontSee(Str::limit($product2->name, 20))
                ->screenshot('filterProductByBrand');
        });
    }

    /** @test */
    public function it_shows_the_product_details_page()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product, $category) {
            $browser->visit('/products/' . $product->slug)
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
                ->screenshot('showDetailProduct');
        });
    }

    /** @test */
    public function the_limit_of_the_increment_button_is_the_max_quantity()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct3($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertButtonEnabled('@incrementButton')
                ->press('@incrementButton')
                ->pause(500)
                ->press('@incrementButton')
                ->pause(500)
                ->assertButtonDisabled('@incrementButton')
                ->screenshot('incrementButton');
        });
    }

    /** @test */
    public function the_limit_of_the_decrement_button_is_one()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct3($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertButtonDisabled('@decrementButton')
                ->press('@incrementButton')
                ->pause(500)
                ->assertButtonEnabled('@decrementButton')
                ->screenshot('decrementButton');
        });
    }

    /** @test */
    public function show_the_select_menu_of_size_or_color_depending_its_subcategory()
    {
        $category1 = $this->createCategory();
        $subcategory1 = $this->createSubcategory($category1->id);
        $brand = $this->createBrand($category1->id);
        $product1 = $this->createProduct3($subcategory1->id, $brand->id);

        $category2 = $this->createCategory();
        $subcategory2 = $this->createSubcategory($category2->id, true);
        $brand = $this->createBrand($category2->id);
        $product2 = $this->createProduct3($subcategory2->id, $brand->id);

        $category3 = $this->createCategory();
        $subcategory3 = $this->createSubcategory($category3->id, true, true);
        $brand = $this->createBrand($category3->id);
        $product3 = $this->createProduct3($subcategory3->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product1, $product2, $product3) {
            $browser->visit('/products/' . $product1->slug)
                ->pause(500)
                ->assertNotPresent('@colorSelect')
                ->assertNotPresent('@sizeSelect')
                ->screenshot('productWithoutColorAndSize');
            $browser->pause(500);
            $browser->visit('/products/' . $product2->slug)
                ->pause(500)
                ->assertPresent('@colorSelect')
                ->assertNotPresent('@sizeSelect')
                ->screenshot('productWithColor');
            $browser->pause(500);
            $browser->visit('/products/' . $product3->slug)
                ->pause(500)
                ->assertPresent('@colorSelect')
                ->assertPresent('@sizeSelect')
                ->screenshot('productWithColorAndSize');
        });
    }
}
