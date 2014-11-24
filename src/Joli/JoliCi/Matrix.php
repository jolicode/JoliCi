<?php
/*
 * This file is part of JoliCi.
 *
 * (c) Joel Wurtz <jwurtz@jolicode.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joli\JoliCi;

/**
 * Create the Job list by computing all available possibility through dimensions
 */
class Matrix
{
    private $dimensions = array();

    /**
     * Set a dimension for this matrix
     *
     * @param string $name   Name of the dimension
     * @param array  $values Value for this dimension
     */
    public function setDimension($name, array $values)
    {
        if (empty($values)) {
            $values = array(null);
        }

        $this->dimensions[$name] = $values;
    }

    /**
     * Return all possibility for the matrix
     *
     * @return array
     */
    public function compute()
    {
        $dimensions = $this->dimensions;

        if (empty($dimensions)) {
            return array();
        }

        // Pop first dimension
        $values = reset($dimensions);
        $name   = key($dimensions);
        unset($dimensions[$name]);

        // Create all possiblites for the first dimension
        $posibilities = array();

        foreach ($values as $v) {
            $posibilities[] = array($name => $v);
        }

        // If only one dimension return simple all the possibilites created (break point of recursivity)
        if (empty($dimensions)) {
            return $posibilities;
        }

        // If not create a new matrix with remaining dimension
        $matrix = new Matrix();

        foreach ($dimensions as $name => $values) {
            $matrix->setDimension($name, $values);
        }

        $result    = $matrix->compute();
        $newResult = array();

        foreach ($result as $value) {
            foreach ($posibilities as $possiblity) {
                $newResult[] = $value + $possiblity;
            }
        }

        return $newResult;
    }
}
