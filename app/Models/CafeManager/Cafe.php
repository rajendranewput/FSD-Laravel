<?php

namespace App\Models\CafeManager;

use Illuminate\Database\Eloquent\Model;

class Cafe extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cafes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
