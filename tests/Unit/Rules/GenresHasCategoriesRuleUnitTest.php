<?php


namespace Tests\Unit\Rules;

use App\Rules\GenresHasCategoriesRule;
use Illuminate\Contracts\Validation\Rule;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class GenresHasCategoriesRuleUnitTest extends TestCase
{
    public function testIfImplementsTestCase()
    {

        $GenresHasCategoriesRule = new GenresHasCategoriesRule([1, 2]);
        $this->assertInstanceOf(Rule::class, $GenresHasCategoriesRule);

    }


    public function testCategoriesIdField()
    {

        $rule = new GenresHasCategoriesRule(
            [1, 1, 2, 2]
        );

//        usando Reflection para poder acessar um atributo privado
        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $categoriesId);
    }

    public function testGenresIdValue()
    {
        $rule = $this->createRuleMock([]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturnNull();
        $rule->passes('', [1, 1, 2, 2]);

        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('genresId');
        $reflectionProperty->setAccessible(true);

        $genresId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $genresId);
    }

    public function testPassesReturnsFalseWhenCategoriesorGenresIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));


    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty()
    {

        $rule = $this->createRuleMock([1]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect());;

        $this->assertFalse($rule->passes('', [1]));

    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenres()
    {
        $rule = $this->createRuleMock([1, 2]);

        $rule->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect(['category_id'=> 1]));

        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid(){

        $rule = $this->createRuleMock([1,2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2],
            ]));

        $this->assertTrue($rule->passes('', [1]));
    }



    public function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GenresHasCategoriesRule::class, [$categoriesId])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

}
