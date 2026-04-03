<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;


class Employee extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ['name', 'document', 'company_id'];
    
    protected $dates = ['deleted_at'];
    
    /**
     * Relacionamento com Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * Relacionamento com Assets (através de atribuições)
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'employee_id');
    }
    
    /**
     * Relacionamento com AssetAssignments (histórico de atribuições)
     */
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'employee_id');
    }
    
    /**
     * Ativos atualmente atribuídos a este colaborador
     */
    public function currentAssets()
    {
        return $this->assets()->where('asset_status', 'atribuido');
    }
    
    /**
     * Número de ativos atribuídos
     */
    public function getAssetsCountAttribute()
    {
        return $this->assets()->count();
    }
    
    /**
     * Valor total dos ativos atribuídos
     */
    public function getTotalAssetsValueAttribute()
    {
        return $this->assets()->sum('total_value');
    }
}