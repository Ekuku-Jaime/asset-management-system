<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['guide', 'date'];
    
    protected $dates = ['date', 'deleted_at'];
    
    /**
     * Validar que a data não é futura
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            if ($shipment->date > now()) {
                throw new \Exception('A data da remessa não pode ser no futuro.');
            }
        });

        static::updating(function ($shipment) {
            if ($shipment->date > now()) {
                throw new \Exception('A data da remessa não pode ser no futuro.');
            }
        });
    }
    
    /**
     * Formatar o guide para exibição
     */
    public function getFormattedGuideAttribute()
    {
        return strtoupper($this->guide);
    }
    
    /**
     * Verificar se a remessa é recente (últimos 7 dias)
     */
    public function getIsRecentAttribute()
    {
        return $this->date->diffInDays(now()) <= 7;
    }
}