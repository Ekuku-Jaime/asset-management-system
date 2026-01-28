<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name'];
    protected $dates = ['deleted_at'];
    
    /**
     * Relacionamento com Requests
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }
    
    /**
     * Contar requisições por tipo
     */
    public function getRequestsCountAttribute()
    {
        return [
            'total' => $this->requests()->count(),
            'internal' => $this->requests()->internal()->count(),
            'external' => $this->requests()->external()->count(),
        ];
    }
}