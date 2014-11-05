<?php

namespace Joli\JoliCi;

class MatrixTest extends \PHPUnit_Framework_TestCase
{
    public function testCompute()
    {
        $matrix = new Matrix();
        $matrix->setDimension('a', array(1, 2, 3));
        $matrix->setDimension('b', array(1, 2, 3));
        $matrix->setDimension('c', array(1, 2, 3));

        $possibilities = $matrix->compute();

        $expected = array(
            array('a' => 1, 'b' => 1, 'c' => 1),
            array('a' => 1, 'b' => 1, 'c' => 2),
            array('a' => 1, 'b' => 1, 'c' => 3),

            array('a' => 1, 'b' => 2, 'c' => 1),
            array('a' => 1, 'b' => 2, 'c' => 2),
            array('a' => 1, 'b' => 2, 'c' => 3),

            array('a' => 1, 'b' => 3, 'c' => 1),
            array('a' => 1, 'b' => 3, 'c' => 2),
            array('a' => 1, 'b' => 3, 'c' => 3),

            array('a' => 2, 'b' => 1, 'c' => 1),
            array('a' => 2, 'b' => 1, 'c' => 2),
            array('a' => 2, 'b' => 1, 'c' => 3),

            array('a' => 2, 'b' => 2, 'c' => 1),
            array('a' => 2, 'b' => 2, 'c' => 2),
            array('a' => 2, 'b' => 2, 'c' => 3),

            array('a' => 2, 'b' => 3, 'c' => 1),
            array('a' => 2, 'b' => 3, 'c' => 2),
            array('a' => 2, 'b' => 3, 'c' => 3),

            array('a' => 3, 'b' => 1, 'c' => 1),
            array('a' => 3, 'b' => 1, 'c' => 2),
            array('a' => 3, 'b' => 1, 'c' => 3),

            array('a' => 3, 'b' => 2, 'c' => 1),
            array('a' => 3, 'b' => 2, 'c' => 2),
            array('a' => 3, 'b' => 2, 'c' => 3),

            array('a' => 3, 'b' => 3, 'c' => 1),
            array('a' => 3, 'b' => 3, 'c' => 2),
            array('a' => 3, 'b' => 3, 'c' => 3),
        );

        $this->assertCount(count($expected), $possibilities);

        foreach ($expected as $value) {
            $this->assertContains($value, $possibilities);
        }
    }
}
