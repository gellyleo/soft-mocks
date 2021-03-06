<?php
/**
 * Test case for Soft Mocks capabilities. Requires phpunit from badoo repo or with accepted pull request
 * https://github.com/sebastianbergmann/phpunit/pull/2116
 *
 * @author Yuriy Nasretdinov <y.nasretdinov@corp.badoo.com>
 */

class ExampleTest extends PHPUnit_Framework_TestCase
{
    const EX_CLASS_CONST = 5;

    public function exampleFact($n)
    {
        if ($n <= 1) return 1;
        return $n * $this->exampleFact($n - 1);
    }

    public function exampleGenerator()
    {
        yield 1;
        yield 2;
    }

    public function tearDown()
    {
        \QA\SoftMocks::restoreAll();
    }

    public function testFunction()
    {
        \QA\SoftMocks::redefineFunction('strlen', '$a', 'return 2;');
        $this->assertEquals(2, strlen("a"));
    }

    public function testConstant()
    {
        define('SOME_CONST', 3);
        \QA\SoftMocks::redefineConstant('SOME_CONST', 4);
        $this->assertEquals(4, SOME_CONST);
    }

    public function testClassConstant()
    {
        \QA\SoftMocks::redefineConstant(self::class . '::EX_CLASS_CONST', 6);
        $this->assertEquals(6, self::EX_CLASS_CONST);
    }

    public function testMethod()
    {
        \QA\SoftMocks::redefineMethod(self::class, 'exampleFact', '$n', 'return -1;');
        $this->assertEquals(-1, $this->exampleFact(4));
        $this->assertEquals(-4, \QA\SoftMocks::callOriginal([$this, 'exampleFact'], [4]));
    }

    public function testGenerator()
    {
        \QA\SoftMocks::redefineGenerator(
            self::class,
            'exampleGenerator',
            function() {
                yield 3;
                yield 4;
                yield 5;
            }
        );

        $all_values = [];
        foreach ($this->exampleGenerator() as $v) {
            $all_values[] = $v;
        }

        $this->assertEquals([3, 4, 5], $all_values);
    }
}
