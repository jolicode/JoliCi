<?php
/*
 * This file is part of JoliCi.
*
* (c) Joel Wurtz <jwurtz@jolicode.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Joli\JoliCi\Log;

use Monolog\Formatter\FormatterInterface;

class SimpleFormatter implements FormatterInterface
{

    /*
     * (non-PHPdoc) @see \Monolog\Formatter\FormatterInterface::format()
     */
    public function format(array $record)
    {
        return $record['message'];
    }

    /*
     * (non-PHPdoc) @see \Monolog\Formatter\FormatterInterface::formatBatch()
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }
}