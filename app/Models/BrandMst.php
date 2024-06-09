<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandMst extends Model
{
    use HasFactory;

    protected $table = 'brands_mst';

    protected $fillable = ['name', 'enabled', 'created_by'];

    public function categories()
    {
        return $this->hasMany(CategoryMst::class, 'brand_id');
    }

    public function stocks()
    {
        return $this->hasMany(StockMst::class, 'brand_id');
    }

    public function admin()
    {
        return $this->belongsTo(AdminMst::class, 'created_by');
    }
}
