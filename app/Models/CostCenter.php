<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory;

    protected $table = 'wn_costcenter';
    protected $fillable = ['team_name', 'sector_name', 'district_name', 'region_name'];

    public function glCodes()
    {
        return $this->hasMany(GlCode::class, 'unit_id', 'team_name');
    }
}

