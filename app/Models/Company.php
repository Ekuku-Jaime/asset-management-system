<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Company extends Model
{
     use HasFactory;
     use SoftDeletes, Auditable;

    protected $fillable = ['name','province','department'];
    protected $dates = ['deleted_at'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Ativos da empresa através dos colaboradores
     */
    public function assets()
    {
        return $this->hasManyThrough(
            Asset::class,
            Employee::class,
            'company_id', // Foreign key on employees table
            'employee_id', // Foreign key on assets table
            'id', // Local key on companies table
            'id' // Local key on employees table
        );
    }
    /**
     * Número de ativos da empresa
     */
    public function getAssetsCountAttribute()
    {
        return $this->assets()->count();
    }
    
    /**
     * Valor total dos ativos da empresa
     */
    public function getTotalAssetsValueAttribute()
    {
        return $this->assets()->sum('total_value');
    }
}
