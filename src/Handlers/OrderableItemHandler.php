<?php

namespace Xpressengine\Plugins\XeroCommerce\Handlers;

use Xpressengine\Plugins\XeroCommerce\Models\OrderableItem;

abstract class OrderableItemHandler
{
    public function getSummary($orderableItems = null)
    {
        if (is_null($orderableItems)) {
            $orderableItems = $this->getSellSetList();
        }

        $origin = $orderableItems->sum(function (OrderableItem $item) {
            return $item->getOriginalPrice();
        });

        $sell = $orderableItems->sum(function (OrderableItem $item) {
            return $item->getSellPrice();
        });

        $fare = $orderableItems->sum(function (OrderableItem $item) {
            return $item->getFare();
        });

        $sum = $sell + $fare;

        return [
            'original_price' => $origin,
            'sell_price' => $sell,
            'discount_price' => $origin - $sell,
            'fare' => $fare,
            'sum' => $sum
        ];
    }

    abstract public function getSellSetList();
}
