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
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        $this->browse(function (Browser $browser) use ($product1, $product2) {
            $browser->visit('/')
                ->assertSee($product1->name)
                ->assertSee($product2->name)
                ->screenshot('showProducts-test');
        });
    }

    public function createProduct($color = false, $size = false) {
        $brand = Brand::factory()->create();

        $category = Category::factory()->create([
            'name' => 'Celulares y tablets'
        ]);
        $category->brands()->attach($brand->id);

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => $color,
            'size' => $size,
        ]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,

        ]);

        Image::factory()->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class,
        ]);
    }
}
