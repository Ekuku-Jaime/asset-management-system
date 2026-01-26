<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['number', 'date'];
    
    protected $dates = ['date', 'deleted_at'];
    
    /**
     * Validar que a data não é futura
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if ($invoice->date > now()) {
                throw new \Exception('A data da fatura não pode ser no futuro.');
            }
        });

        static::updating(function ($invoice) {
            if ($invoice->date > now()) {
                throw new \Exception('A data da fatura não pode ser no futuro.');
            }
        });
    }
}