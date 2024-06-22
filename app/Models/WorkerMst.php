<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerMst extends Model
{
    use HasFactory;
    protected $table="worker_mst";
    protected $fillable = [
        "name",
        "phone",
        "pin",
        "created_by",
        "enabled",
        "token",
        "created_at",
        "updated_at"
    ];
    protected $casts = [
        "enabled" => "boolean",
        "pin"=>"integer"
    ];

    protected $appends = [
        "total_sale","last_30_sale","total_money","last_30_money"
    ];

    public function admin()
    {
        return $this->hasOne(AdminMst::class, 'id','created_by');
    }

    public function stocks()
    {
        return $this->hasMany(StockMst::class,'sold_by');
    }

    private function getTotalSaleAttribute(){
        return $this->stocks()->count();
    }

    private function getLast30Sale(){
        return $this->stocks()->whereBetween('created_at', [date('Y-m-d', strtotime('-30 days')), date('Y-m-d')])->count();
    }

    private function getTotalMoneyAttribute(){
        return $this->stocks()->sum('mrp');
    }

    private function getLast30MoneyAttribute(){
        return $this->stocks()->whereBetween('created_at', [date('Y-m-d', strtotime('-30 days')), date('Y-m-d')])->sum('mrp');
    }

}
