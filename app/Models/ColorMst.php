<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorMst extends Model
{
    use HasFactory;

    protected $table = 'colors_mst';

    protected $fillable = ['name', 'code', 'enabled', 'created_by'];
    protected $casts = [
        'enabled' => 'boolean'
    ];




    public function stocks()
    {
        return $this->hasMany(StockMst::class, 'color_id');
    }

    public function admin()
    {
        return $this->belongsTo(AdminMst::class, 'created_by');
    }
}
