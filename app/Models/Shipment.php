<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['guide', 'date', 'status'];
    
    protected $dates = ['date', 'deleted_at'];
    
    protected $attributes = [
        'status' => 'incompleto',
    ];
    
    /**
     * Relacionamento com documentos
     */
    public function documents()
    {
        return $this->hasMany(ShipmentDocument::class);
    }
    
    /**
     * Verificar se tem documentos
     */
    public function hasDocuments()
    {
        return $this->documents()->count() > 0;
    }
    
    /**
     * Atualizar status baseado em documentos
     */
    public function updateStatus()
    {
        $this->status = $this->hasDocuments() ? 'completo' : 'incompleto';
        $this->save();
    }
    
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
        
        // Atualizar status automaticamente
        static::saved(function ($shipment) {
            // Se o status não foi definido manualmente, atualiza com base em documentos
            if ($shipment->wasChanged() && !$shipment->wasChanged('status')) {
                $shipment->updateStatus();
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