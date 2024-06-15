<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMst extends Model
{
    use HasFactory;

    protected $table = 'categories_mst';

    protected $fillable = ['name', 'brand_id', 'enabled', 'img', 'created_by'];
    protected $casts = [
        'enabled'=>'boolean'
    ];
    protected $appends = ['stock_count', 'sold_products', 'available_products'];


    public function brand()
    {
        return $this->belongsTo(BrandMst::class, 'brand_id');
    }

    public function stocks()
    {
        return $this->hasMany(StockMst::class, 'category_id');
    }

    public function admin()
    {
        return $this->belongsTo(AdminMst::class, 'created_by');
    }
    public function getStockCountAttribute()
    {
        return $this->stocks()->count();
    }

    public function getSoldProductsAttribute()
    {
        return $this->stocks()->where('is_sold', true)->count();
    }

    public function getAvailableProductsAttribute()
    {
        return $this->stocks()->where('is_sold', false)->count();
    }



}
