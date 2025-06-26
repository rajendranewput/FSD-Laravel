<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';
    protected $fillable = ['account_id', 'name'];

    public function cafes()
    {
        return $this->hasMany(Cafe::class, 'account_id', 'account_id');
    }
}

