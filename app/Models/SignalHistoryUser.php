<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SignalHistoryUser extends Pivot
{

    protected $casts = [
        'before_asset' => 'array',
        'after_asset' => 'array',
    ];
}
