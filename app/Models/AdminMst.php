<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMst extends Model
{
    use HasFactory;
    protected $table="admin_mst";
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'token',
        'enabled',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        "enabled"=>'boolean'
    ];

    protected $hidden =[
        'password'
    ];


    public function workers()
    {
        return $this->hasMany(WorkerMst::class, 'created_by');
    }

}
