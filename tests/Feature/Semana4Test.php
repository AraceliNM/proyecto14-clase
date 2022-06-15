<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\CreateProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class Semana4Test extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save');

        $this->assertDatabaseHas('products', [
            'name' => 'Product',
            'slug' => 'product',
            'description' => 'description',
            'price' => 20,
            'quantity' => 5
        ]);
    }

    /** @test */
    public function create_a_product_with_color()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product2')
            ->set('slug', 'product2')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->call('save');

        $this->assertDatabaseHas('products', [
            'name' => 'Product2',
            'slug' => 'product2',
            'description' => 'description',
            'price' => 20,
        ]);
    }

    /** @test */
    public function create_a_product_with_color_and_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product3')
            ->set('slug', 'product3')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->call('save');

        $this->assertDatabaseHas('products', [
            'name' => 'Product3',
            'slug' => 'product3',
            'description' => 'description',
            'price' => 20,
        ]);
    }

    /** @test */
    public function the_category_field_is_required()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', '')
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['category_id' => 'required']);
    }

    /** @test */
    public function the_subcategory_field_is_required()
    {
        $category = $this->createCategory();
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', '')
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['subcategory_id' => 'required']);
    }

    /** @test */
    public function the_name_field_is_required()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', '')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function the_slug_field_is_required()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', '')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['slug' => 'required']);
    }

    /** @test */
    public function the_slug_field_is_unique()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'slug-no-unico')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save');

        $this->assertDatabaseCount('products', 1);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('brand_id', $brand->id)
            ->set('name', 'Product')
            ->set('slug', 'slug-no-unico')
            ->set('description', 'description')
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['slug' => 'unique']);

        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function the_description_field_is_required()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', '')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['description' => 'required']);
    }

    /** @test */
    public function the_brand_field_is_required()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', '')
            ->set('brand_id', '')
            ->set('price', 20)
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['brand_id' => 'required']);
    }

    /** @test */
    public function the_price_field_is_required()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', '')
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['price' => 'required']);
    }

    /** @test */
    public function the_quantity_field_is_required_when_creating_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', '')
            ->call('save')
            ->assertHasErrors(['quantity' => 'required']);
    }

    /** @test */
    public function the_quantity_field_is_optional_when_creating_a_product_with_color()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', '')
            ->call('save')
            ->assertHasNoErrors(['quantity']);

        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function the_quantity_field_is_optional_when_creating_a_product_with_color_and_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('name', 'Product')
            ->set('slug', 'product')
            ->set('description', 'description')
            ->set('brand_id', $brand->id)
            ->set('price', 20)
            ->set('quantity', '')
            ->call('save')
            ->assertHasNoErrors(['quantity']);

        $this->assertDatabaseCount('products', 1);
    }
}
