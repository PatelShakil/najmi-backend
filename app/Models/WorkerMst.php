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

    public function admin()
    {
        return $this->hasOne(AdminMst::class, 'id','created_by');
    }


}
