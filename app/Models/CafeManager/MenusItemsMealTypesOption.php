<?php

namespace App\Models\CafeManager;

use Illuminate\Database\Eloquent\Model;

class MenusItemsMealTypesOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus_items_meal_types_options';
    protected $primaryKey = 'meal_type_id';
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function cafe()
    {
        return $this->hasOne(Cafe::class, 'cafe_id', 'cafe_id');
    }

}
