<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Semana1Test extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_a_category_when_the_navigation_menu_is_clicked()
    {
      $category = Category::factory()->create([
          'name' => 'Celulares y tablets',
      ]);

        $this->browse(function (Browser $browser) use ($category) {
            $browser->visit('/')
                    ->click('@showCategory')
                    ->assertSee($category->name)
                    ->screenshot('showCategory');
        });
    }

    /** @test */
    public function it_shows_a_subcategory_when_the_navigation_menu_is_clicked_and_the_mouse_is_over_a_category()
    {
        $category = Category::factory()->create([
            'name' => 'Celulares y tablets',
        ]);
        $subcategory = Subcategory::factory()->create([
            'name' => 'Celulares y smartphones',
        ]);

        $this->browse(function (Browser $browser) use ($category, $subcategory) {
            $browser->visit('/')
                ->click('@showCategory')
                ->mouseover('@category')
                ->assertSee($subcategory->name)
                ->screenshot('showSubcategory');
        });
    }
}
