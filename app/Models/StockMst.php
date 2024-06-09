<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMst extends Model
{
    use HasFactory;

    protected $table = 'stock_mst';

    protected $primaryKey = 'barcode_no';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['barcode_no', 'name', 'brand_id', 'category_id', 'color_id', 'mrp', 'created_by', 'is_sold', 'sold_by'];

    public function brand()
    {
        return $this->belongsTo(BrandMst::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryMst::class, 'category_id');
    }

    public function color()
    {
        return $this->belongsTo(ColorMst::class, 'color_id');
    }

    public function admin()
    {
        return $this->belongsTo(AdminMst::class, 'created_by');
    }

    public function worker()
    {
        return $this->belongsTo(WorkerMst::class, 'sold_by');
    }
}
