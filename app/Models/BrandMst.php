<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandMst extends Model
{
    use HasFactory;

    protected $table = 'brands_mst';

    protected $fillable = ['name', 'enabled', 'created_by'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected $appends = ['category_count', 'stock_count', 'sold_products', 'available_products'];

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

    public function getCategoryCountAttribute()
    {
        return $this->categories()->count();
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
