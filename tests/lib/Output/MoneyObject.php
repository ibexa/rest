<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

class MoneyObject implements \JsonSerializable
{
    private int $amount;

    private string $currency;

    public function __construct(int $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
