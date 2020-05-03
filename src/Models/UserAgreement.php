<?php

namespace Xpressengine\Plugins\XeroCommerce\Models;

use Xpressengine\Database\Eloquent\DynamicModel;

class UserAgreement extends DynamicModel
{
    protected $table = 'xero_commerce__user_agreement';

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
