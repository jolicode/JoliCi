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
     * Recursive copy
     *
     * @param string  $originFile Original file or directory
     * @param string  $targetFile Target file or directory
     * @param boolean $override   Does it override existing file ?
     */
    public function rcopy($originFile, $targetFile, $override = false)
    {
        $cwd = getcwd();

        if (!is_dir($originFile)) {
            $this->copy($originFile, $targetFile, $override);
        }

        $directory = opendir($originFile);

        if (!file_exists($targetFile)) {
            mkdir($targetFile, 0755, true);
        }

        while (false !== ($file = readdir($directory))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($originFile . DIRECTORY_SEPARATOR . $file)) {
                    $this->rcopy($originFile . DIRECTORY_SEPARATOR . $file, $targetFile . DIRECTORY_SEPARATOR . $file, $override);
                } elseif (is_link($originFile . DIRECTORY_SEPARATOR . $file)) {
                    if (!file_exists($targetFile . DIRECTORY_SEPARATOR . $file) || $override) {
                        //Read link
                        $link = readlink($originFile . DIRECTORY_SEPARATOR . $file);
                        //Change to dir of target link (to work with symlink)
                        chdir(dirname($targetFile . DIRECTORY_SEPARATOR . $file));
                        //Write link
                        symlink($link, $targetFile . DIRECTORY_SEPARATOR . $file);
                        //Rewind to current dir
                        chdir($cwd);
                    }
                } else {
                    $this->copy($originFile . DIRECTORY_SEPARATOR . $file, $targetFile . DIRECTORY_SEPARATOR . $file, $override);
                }
            }
        }

        closedir($directory);
    }

    /**
     * Add keeping same permissions as origin file
     *
     * @see \Symfony\Component\Filesystem\Filesystem::copy()
     * @param string $originFile
     * @param string $targetFile
     * @param Boolean $override
     */
    public function copy($originFile, $targetFile, $override = false)
    {
        parent::copy($originFile, $targetFile, $override);

        $this->chmod($targetFile, fileperms($originFile));
    }
}
