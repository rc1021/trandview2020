<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SignalHistoryUser extends Pivot
{

    protected $casts = [
        'asset' => 'array',
    ];
}
