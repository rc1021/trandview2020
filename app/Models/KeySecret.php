<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\AdminUser;

class KeySecret extends Model
{
    use HasFactory;

    protected $fillable = ['alias', 'type', 'key', 'secret'];

    public function admin()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
