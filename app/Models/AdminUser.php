<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\Administrator;
use App\Models\KeySecret;

class AdminUser extends Administrator
{

    public function keysecrets()
    {
        return $this->hasMany(KeySecret::class, 'user_id');
    }
}
