<?php

namespace Mirko\Newsletter\Tca;

use Mirko\Newsletter\Tools;

/**
 * Handle bounced account encryption
 */
class BounceAccountTca
{
    /**
     * Encrypts the field value
     *
     * @param string $value the field value to be evaluated
     *
     * @return string
     */
    public function evaluateFieldValue(string $value): string
    {
        return Tools::encrypt($value);
    }
}
