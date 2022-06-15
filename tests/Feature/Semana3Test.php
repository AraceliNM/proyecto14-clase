<?php

namespace Tests\Feature;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Auth\Events\Login;
use App\Listeners\MergeTheCart;
use App\Http\Livewire\AddCartItem;
use App\Http\Livewire\AddCartItemColor;
use App\Http\Livewire\AddCartItemSize;
use App\Http\Livewire\Search;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\TestCase;

class Semana3Test extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_see_the_available_stock_of_product()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $subcategoryColor = $this->createSubcategory($category->id, true);
        $subcategoryColorSize = $this->createSubcategory($category->id, true, true);

        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product1 = $this->createProduct($subcategory->id, $brand->id);
        $product2 = $this->createProduct($subcategoryColor->id, $brand->id, Product::PUBLICADO,  array($color));
        $product3 = $this->createProduct($subcategoryColorSize->id, $brand->id, Product::PUBLICADO,  array($color));

        $size = $this->createSize($product3->id, array($color));

        $this->get('products/' . $product1->slug)
            ->assertStatus(200)
            ->assertSeeText('Stock disponible: 15')
            ->assertSee($product1->quantity);

        $this->assertEquals(qty_available($product1->id), 15);

        Livewire::test(AddCartItemColor::class, ['product' => $product2])
            ->set('options', ['color_id' => $color->id])
            ->call('addItem');

        $this->assertEquals(qty_available($product2->id, $color->id), 9);


        Livewire::test(AddCartItemSize::class, ['product' => $product3])
            ->set('options', ['size_id' => $size->id, 'color_id' => $color->id])
            ->call('addItem');

        $this->assertEquals(qty_available($product3->id, $color->id, $size->id), 11);
    }

    /** @test */
    public function filter_search_by_name_and_show_nothing_if_input_is_empty()
    {
        $category = Category::factory()->create();

        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'color' => false,
            'size' => false,
        ]);

        $brand = Brand::factory()->create();
        $category->brands()->attach([$brand->id]);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'mando',
            'brand_id' => $brand->id,
            'quantity' => 3
        ]);

        Image::factory(3)->create([
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
        ]);

        $product2 = Product::factory()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'auricular',
            'brand_id' => $brand->id,
            'quantity' => 2
        ]);

        Image::factory(2)->create([
            'imageable_id' => $product2->id,
            'imageable_type' => Product::class
        ]);

        Livewire::test(Search::class)
            ->set('search', 'man')
            ->assertSee($product->name)
            ->assertDontSee($product2->name);

        Livewire::test(Search::class)
            ->set('search', '')
            ->assertDontSee($product->name)
            ->assertDontSee($product2->name);
    }

    /** @test EJERCICIO 2 */
    public function save_the_cart_to_the_database_when_the_user_logout()
    {
        $user = User::factory()->create();

        $product = $this->createProductAll();
        $price = $product->price;

        $product2 = $this->createProductAll(false, false, Product::PUBLICADO, 3);
        $price2 = $product2->price;

        $this->actingAs($user);

        Livewire::test(AddCartItem::class, ['product' => $product])
            ->call('addItem', $product);

        Livewire::test(AddCartItem::class, ['product' => $product2])
            ->call('addItem', $product2);

        $content = Cart::content();

        $this->post('/logout');

        $this->assertDatabaseHas('shoppingcart', ['content' => serialize($content)]);

        $cart = new MergeTheCart();
        $userLogin = new Login('web', $user, true);
        $this->actingAs($user);

        $cart->handle($userLogin);

        $this->get('/orders/create')
            ->assertStatus(200)
            ->assertSee($product->name)
            ->assertSee($price)
            ->assertSee($product->quantity)
            ->assertSee($product2->name)
            ->assertSee($price2)
            ->assertSee($product2->quantity);
    }
}
