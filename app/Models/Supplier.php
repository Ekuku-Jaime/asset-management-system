<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes, Auditable;

    protected $fillable = ['name', 'nuit'];
    
    protected $dates = ['deleted_at'];
    
    /**
     * Relacionamento com Requests
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }
    
    /**
     * Relacionamento com Assets através de Requests
     */
    public function assets()
    {
        return $this->hasManyThrough(
            Asset::class,
            Request::class,
            'supplier_id', // Foreign key on requests table
            'request_id',   // Foreign key on assets table
            'id',           // Local key on suppliers table
            'id'            // Local key on requests table
        );
    }
}