<?php

namespace Rapid\Laplus\Tests\Guide\Models;

use Rapid\Laplus\Label\LabelTranslator;

class _TestLabel1ForGuide extends LabelTranslator
{

    /**
     * Test name
     *
     * @return string
     */
    public function name()
    {
        return '';
    }

    /**
     * Money Test
     *
     * @param string $currency
     * @return string
     */
    public function money(string $currency = '$')
    {
        return $currency;
    }

    /**
     * @param int $max
     * @return string
     */
    public function friends(int $max)
    {
        return (string)$max;
    }

}