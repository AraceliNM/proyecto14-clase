<?php

namespace Tests\Browser;

use App\Models\Image;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Semana4Test extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function some_routes_cannot_be_accessed_without_being_logged_in()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) {
            $browser->visit('/orders')
                ->assertPathIs('/login')
                ->pause(500)
                ->visit('/orders/create')
                ->assertPathIs('/login')
                ->pause(500)
                ->screenshot('routesCannotBeAccessedWithoutBeingLoggedIn')
                ->pause(500)
                ->loginAs(User::factory()->create())
                ->visit('/orders')
                ->assertPathIsNot('/login')
                ->pause(500)
                ->visit('/orders/create')
                ->assertPathIsNot('/login')
                ->pause(500)
                ->screenshot('routesThatCanBeAccessedByLoggingIn');
        });
    }

    /** @test */
    public function a_user_cannot_access_an_order_from_another_user()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $user2, $product) {
            $browser->loginAs($user)
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->type('@contactName', 'Araceli')
                ->pause(500)
                ->type('@contactPhone', '12345689')
                ->radio('envio_type', '1')
                ->pause(500)
                ->click('@createOrder')
                ->pause(500)
                ->logout();

            $order = Order::first();

            $browser->loginAs($user2)
                ->visit('/orders/' . $order->id . '/payment')
                ->pause(500)
                ->assertSee('ESTA ACCIÓN NO ESTÁ AUTORIZADA.')
                ->pause(500)
                ->screenshot('aUserCannotAccessAnOrderFromAnotherUser');
        });
    }

    /** @test */
    public function check_the_option_my_orders_when_click_in_the_perfil()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->type('@contactName', 'Araceli')
                ->pause(500)
                ->type('@contactPhone', '12345689')
                ->pause(500)
                ->click('@createOrder')
                ->pause(500)
                ->click('@perfil')
                ->pause(500)
                ->press('@myOrders')
                ->pause(500)
                ->assertPathIs('/orders')
                ->screenshot('checkTheOptionMyOrders');
        });
    }

    /** @test */
    public function the_stock_changes_when_adding_any_product_to_the_cart()
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

        $this->browse(function (Browser $browser) use ($product1, $product2, $product3) {
            $browser->visit('/products/' . $product1->slug)
                ->assertSeeIn('@stock', 15)
                ->click('@addItemButton')
                ->pause(500)
                ->assertSeeIn('@stock', 14)
                ->screenshot('stockDecrementOrder');

            $browser->visit('/products/' . $product2->slug)
                ->select('@colorSelect', 1)
                ->pause(500)
                ->assertSeeIn('@colorStock', 10)
                ->click('@addCartItemColor')
                ->pause(500)
                ->assertSeeIn('@colorStock', 9)
                ->screenshot('stockDecrementOrderColor');

            $browser->visit('/products/' . $product3->slug)
                ->select('@sizeSelect', 1)
                ->pause(500)
                ->select('@colorSelect', 1)
                ->pause(500)
                ->assertSeeIn('@sizeStock', 12)
                ->click('@addCartItemSize')
                ->pause(500)
                ->assertSeeIn('@sizeStock', 11)
                ->screenshot('stockDecrementOrderColorSize');
        });
    }

    /** @test */
    public function stock_changes_in_the_DB_when_an_order_is_created()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'quantity' => $product->quantity
            ]);

            $browser->loginAs($user)
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->screenshot('stockDecrementInTheDB')
                ->visit('/orders/create')
                ->type('@contactName', 'Araceli')
                ->pause(500)
                ->type('@contactPhone', '12345689')
                ->pause(500)
                ->click('@createOrder')
                ->pause(500);

            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'quantity' => $product->quantity -1
            ]);
        });
    }

    /** @test */
    public function orders_canceled_after_10_minutes()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);
        $product2 = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->browse(function (Browser $browser) use ($user, $product, $product2) {
            $browser->loginAs($user)
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->type('@contactName', 'Pedido1')
                ->pause(500)
                ->type('@contactPhone', '12345689')
                ->pause(500)
                ->click('@createOrder')
                ->pause(500)
                ->visit('/products/' . $product2->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->type('@contactName', 'Pedido2')
                ->pause(500)
                ->type('@contactPhone', '12345689')
                ->pause(500)
                ->click('@createOrder')
                ->pause(500);

            $order = Order::first();
            $order->created_at = now()->subMinutes(11);
            $order->save();

            $this->artisan('schedule:run');

            $order2 = Order::latest()->get()->first();

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'user_id' => $user->id,
                'status' => '5'
            ]);

            $this->assertDatabaseHas('orders', [
                'id' => $order2->id,
                'user_id' => $user->id,
                'status' => '1'
            ]);

            $browser->loginAs($user)
                ->visit('/orders')
                ->pause(500)
                ->screenshot('ordersCanceledAfter10Minutes');
        });
    }

    /** @test */
    public function filter_search_by_name_in_admin_finder()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);

        $brand = $this->createBrand($category->id);

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

        $user = $this->createAdminUser();

        $this->browse(function (Browser $browser) use ($user, $product, $product2) {
            $browser->loginAs($user)
                ->visit('/admin')
                ->assertSee($product->name)
                ->assertSee($product2->name)
                ->pause(500)
                ->type('@adminSearch', 'auric')
                ->pause(500)
                ->assertDontSee($product->name)
                ->assertSee($product2->name)
                ->screenshot('filterSearchByNameInAdminFinder');
        });
    }
}
