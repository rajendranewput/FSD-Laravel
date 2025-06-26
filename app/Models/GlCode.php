<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlCode extends Model
{
    use HasFactory;

    protected $table = 'gl_codes';
    protected $fillable = ['unit_id', 'exp_1', 'amount', 'end_date', 'processing_year'];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'unit_id', 'team_name');
    }
}
