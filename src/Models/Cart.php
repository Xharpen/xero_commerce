<?php

namespace Xpressengine\Plugins\XeroCommerce\Models;

class Cart extends SellSet
{
    protected $table = 'xero_commerce__carts';

    public function sellGroups()
    {
        return $this->hasMany(CartGroup::class);
    }

    /**
     * @return array
     */
    public function renderInformation()
    {
        return $this->forcedSellType()->renderForSellSet($this);
    }

    function getJsonFormat()
    {
        return [
            'id' => $this->id,
            'info' => $this->renderInformation(),
            'original_price' => $this->getOriginalPrice(),
            'sell_price' => $this->getSellPrice(),
            'discount_price' => $this->getDiscountPrice(),
            'fare' => $this->getFare(),
            'count' => $this->getCount(),
            'src' => $this->getThumbnailSrc(),
            'url'=> route('xero_commerce::product.show', ['strSlug' => $this->forcedSellType()->getSlug()]),
            'option_list' => $this->forcedSellType()->sellUnits->map(function (sellUnit $sellUnit) {
                return $sellUnit->getJsonFormat();
            }),
            'choose' => $this->sellGroups->map(function (SellGroup $sellGroup) {
                return $sellGroup->getJsonFormat();
            }),
            'name' => $this->forcedSellType()->getName(),
            'shop_carrier' => $this->forcedSellType()->getShopCarrier(),
            'pay' => $this->getShippingFee(),
            'min'=>$this->forcedSellType()->min_buy_count,
            'max'=>$this->forcedSellType()->max_buy_count
        ];
    }
}
