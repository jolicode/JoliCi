<?php
/*
 * This file is part of JoliCi.
 *
 * (c) Joel Wurtz <jwurtz@jolicode.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Joli\JoliCi\Filesystem;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{
    /**
     * Add keeping same permissions as origin file
     *
     * @see \Symfony\Component\Filesystem\Filesystem::copy()
     *
     * @param string  $originFile
     * @param string  $targetFile
     * @param Boolean $override
     */
    public function copy($originFile, $targetFile, $override = false)
    {
        parent::copy($originFile, $targetFile, $override);

        $this->chmod($targetFile, fileperms($originFile));
    }
}
