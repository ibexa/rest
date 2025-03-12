<?php

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