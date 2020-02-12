<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Traits\Uuid;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    private $category;

//    executado uma vez antes do teste
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

    }

//    executado antes de cada assertion do teste
    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();

    }

//    executado ao fim de cada assertion do teste
    protected function tearDown(): void
    {
        parent::tearDown();

    }

//    chamado uma vez ao final do teste
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }


    public function testFillableAttribute()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testIfUseTraits()
    {

        $traits = [
            SoftDeletes::class, Uuid::class
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);

    }

    public function testCastsAttribute()
    {
        $casts = [
            'id' => 'string',
            'is_active' => 'boolean'
        ];
        $this->assertEquals($casts, $this->category->getCasts());

    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->category->incrementing);

    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_ata', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->category->getDates());
        }
        $this->assertCount(count($dates), $this->category->getDates());
    }
}
