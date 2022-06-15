<?php

namespace Tests\Browser;

use App\Http\Livewire\AddCartItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Department;
use App\Models\District;
use App\Models\Image;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\DuskTestCase;

class Semana3Test extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function add_product_without_color_or_size_in_shopping_cart()
    {
        $product = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertPresent('@addItemButton')
                ->click('@addItemButton')
                ->pause(500)
                ->click('@shoppingCart')
                ->screenshot('addProduct');
        });
    }

    /** @test */
    public function add_product_with_color_in_shopping_cart()
    {
        $product = $this->createProductAll(true, false);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertPresent('@colorSelect')
                ->pause(500)
                ->click('@colorSelect')
                ->pause(500)
                ->click('@color', 1)
                ->pause(500)
                ->click('@addCartItemColor')
                ->pause(500)
                ->click('@shoppingCart')
                ->screenshot('addProductWithColor');
        });
    }

    /** @test */
    public function add_product_with_color_and_size_in_shopping_cart()
    {
        $product = $this->createProductAll(true, true);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertPresent('@sizeSelect')
                ->assertPresent('@colorSelect')
                ->pause(500)
                ->click('@sizeSelect')
                ->pause(500)
                ->click('@size', 1)
                ->pause(500)
                ->click('@colorSelect')
                ->pause(500)
                ->click('@colorSize', 1)
                ->pause(500)
                ->click('@addCartItemSize')
                ->pause(500)
                ->click('@shoppingCart')
                ->screenshot('addProductWithColorAndSize');
        });
    }

    /** @test */
    public function the_red_circle_increments_when_adding_a_product_in_cart()
    {
        $product = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertPresent('@addItemButton')
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/')
                ->assertSee('1')
                ->screenshot('redCircleIncrements');
        });
    }

    /** @test */
    public function it_cannot_add_to_cart_over_the_max_quantity_the_product()
    {
        $product = $this->createProductAll(false, false, Product::PUBLICADO, 3);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertPresent('@addItemButton')
                ->click('@incrementButton')
                ->click('@incrementButton')
                ->click('@incrementButton')
                ->click('@addItemButton')
                ->pause(500)
                ->assertDisabled('@addItemButton')
                ->screenshot('cannotAddMoreThanStockAvaible');
        });
    }

    /** @test */
    public function show_the_products_in_cart_view()
    {
        $product = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->assertPresent('@addItemButton')
                ->click('@addItemButton')
                ->pause(500)
                ->click('@shoppingCart')
                ->click('@dropdownCart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->assertSee($product->price)
                ->assertSee('Total: ' . $product->price . ' €')
                ->screenshot('showTheProductsInCartView');
        });
    }

    /** @test */
    public function change_the_product_quantity_in_cart_view()
    {
        $product = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->click('@shoppingCart')
                ->pause(500)
                ->click('@dropdownCart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->click('@cartIncrementButton')
                ->pause(500)
                ->assertSee('Total: ' . $product->price * 2 . ' €')
                ->pause(500)
                ->screenshot('changeTheProductQuantity');
        });
    }

    /** @test */
    public function delete_a_products_and_empty_the_cart()
    {
        $product = $this->createProductAll();
        $product2 = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product, $product2) {
            $browser->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/products/' . $product2->slug)
                ->pause(500)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/shopping-cart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->assertSee($product2->name)
                ->pause(500)
                ->click('@deleteProduct')
                ->pause(500)
                ->assertDontSee($product->name)
                ->assertSee($product2->name)
                ->screenshot('deleteProductsInCart');

            $browser->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/products/' . $product2->slug)
                ->pause(500)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/shopping-cart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->assertSee($product2->name)
                ->pause(500)
                ->click('@destroyCart')
                ->pause(500)
                ->assertDontSee($product->name)
                ->assertDontSee($product2->name)
                ->screenshot('destroyCart');
        });
    }

    /** @test */
    public function an_authenticated_user_can_create_an_order()
    {
        $product = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/shopping-cart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->click('@OrderCreate')
                ->assertPathIs('/login')
                ->screenshot('notLoggedUserCannotCreateAnOrder');

            $browser->LoginAs(User::factory()->create())
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/shopping-cart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->click('@OrderCreate')
                ->assertPathIs('/orders/create')
                ->screenshot('loggedUserCanCreateAnOrder');
        });
    }

    /** @test */
    public function save_the_cart_to_the_database_when_the_user_logout()
    {
        $user = User::factory()->create();

        $product = $this->createProductAll();

        $this->assertDatabaseCount('shoppingcart', 0);

        $this->browse(function (Browser $browser) use ($product, $user) {
            $browser->loginAs($user)
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/shopping-cart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->screenshot('saveCartInDB')
                ->pause(500)
                ->logout();

            $this->assertDatabaseCount('shoppingcart', 1);

            $browser->loginAs($user)
                ->visit('/shopping-cart')
                ->pause(500)
                ->assertSee('CARRITO DE COMPRAS')
                ->assertSee($product->name)
                ->screenshot('cartSavedInDBwhenUserLogsInAgain');
        });
    }

    /** @test */
    public function the_two_types_of_shipping()
    {
        $product = $this->createProductAll();

        $this->assertDatabaseCount('shoppingcart', 0);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->loginAs(User::factory()->create())
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->pause(500)
                ->radio('envio_type', '1')
                ->pause(500)
                ->assertDontSee('Departamento')
                ->assertDontSee('Ciudad')
                ->assertDontSee('Distrito')
                ->assertDontSee('Dirección')
                ->assertDontSee('Referencia')
                ->screenshot('notShowTheFormWhenIClickOnPickUp')
                ->pause(500)
                ->radio('envio_type', '2')
                ->pause(500)
                ->assertSee('Departamento')
                ->assertSee('Ciudad')
                ->assertSee('Distrito')
                ->assertSee('Dirección')
                ->assertSee('Referencia')
                ->screenshot('showTheFormWhenIClickOnDelivery');
        });
    }

    /** @test */
    public function create_an_order_and_destroy_the_cart()
    {
        $product = $this->createProductAll();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->loginAs(User::factory()->create())
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->pause(500)
                ->type('@contactName', 'Araceli')
                ->type('@contactPhone', '12345689')
                ->radio('envio_type', '1')
                ->pause(500)
                ->click('@createOrder')
                ->pause(500)
                ->assertPathIs('/orders/1/payment')
                ->pause(500)
                ->screenshot('createAnOrderAndItRedirectsToTheNewRoute')
                ->pause(500)
                ->visit('/shopping-cart')
                ->pause(500)
                ->screenshot('createAnOrderAndDestroyTheCart');
        });
    }

    /** @test */
    public function departments_select_has_all_departments()
    {
        $product = $this->createProductAll();

        $departments = Department::factory(2)->create()->pluck('id')->all();

        $this->browse(function (Browser $browser) use ($product, $departments) {
            $browser->loginAs(User::factory()->create())
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->pause(500)
                ->radio('envio_type', '2')
                ->pause(500)
                ->click('@departmentSelect')
                ->pause(500)
                ->assertSelectHasOptions('departments', $departments)
                ->screenshot('departmentSelectHasAllDepartments');
        });
    }

    /** @test */
    public function cities_select_has_correct_cities()
    {
        $product = $this->createProductAll();

        $departments = Department::factory(2)->create();

        $cities1= City::factory(2)->create([
            'department_id'=> $departments[0]->id
        ]);
        $cities2= City::factory(2)->create([
            'department_id'=> $departments[1]->id
        ]);

        $idCities1 = $cities1->pluck('id')->all();
        $idCities2 = $cities2->pluck('id')->all();

        $this->browse(function (Browser $browser) use ($product, $departments, $idCities1, $idCities2) {
            $browser->loginAs(User::factory()->create())
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->pause(500)
                ->radio('envio_type', '2')
                ->pause(500)
                ->click('@departmentSelect')
                ->pause(500)
                ->click('@department', '1')
                ->pause(500)
                ->click('@citySelect')
                ->assertSelectHasOptions('cities', $idCities1)
                ->assertSelectMissingOptions('cities', $idCities2)
                ->screenshot('citiesSelectHasCorrectCities');
        });
    }

    /** @test */
    public function districts_select_has_correct_districts()
    {
        $product = $this->createProductAll();

        $departments = Department::factory(2)->create();

        $cities= City::factory(2)->create([
            'department_id'=> $departments[0]->id
        ]);

        $districts1 = District::factory(2)->create([
            'city_id' => $cities[0]
        ]);
        $districts2 = District::factory(2)->create([
            'city_id' => $cities[1]
        ]);

        $idDistricts1 = $districts1->pluck('id')->all();
        $idDistricts2 = $districts2->pluck('id')->all();

        $this->browse(function (Browser $browser) use ($product, $departments, $idDistricts1, $idDistricts2) {
            $browser->loginAs(User::factory()->create())
                ->visit('/products/' . $product->slug)
                ->click('@addItemButton')
                ->pause(500)
                ->visit('/orders/create')
                ->pause(500)
                ->radio('envio_type', '2')
                ->pause(500)
                ->click('@departmentSelect')
                ->click('@department', '1')
                ->pause(500)
                ->click('@citySelect')
                ->click('@city', '1')
                ->pause(500)
                ->click('@districtSelect')
                ->pause(500)
                ->assertSelectHasOptions('districts', $idDistricts1)
                ->assertSelectMissingOptions('districts', $idDistricts2)
                ->screenshot('districtsSelectHasCorrectDistricts');
        });
    }
}
