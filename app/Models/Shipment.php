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
            'shipment_id', // Foreign key on requests table
            'request_id',   // Foreign key on assets table
            'id',           // Local key on shipments table
            'id'            // Local key on requests table
        );
    }
    
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
        $oldStatus = $this->status;
        
        if ($this->hasDocuments()) {
            $this->status = 'completo';
           
        } else {
            $this->status = 'incompleto';
            
        }
        
        // Se o status mudou, guardar e atualizar requisições relacionadas
        if ($this->isDirty('status')) {
            $this->saveQuietly(); // Evita loop
            
            // Atualizar todas as requisições relacionadas a esta remessa
            $this->updateRelatedRequests();
        } else {
            $this->saveQuietly();
        }
    }

        /**
     * Atualizar todas as requisições relacionadas a esta remessa
     */
    public function updateRelatedRequests()
    {
        foreach ($this->requests as $request) {
            $request->updateProcessStatus();
            $request->save();
            
            // Atualizar também os ativos relacionados a estas requisições
            foreach ($request->assets as $asset) {
                $asset->updateProcessStatus();
                $asset->save();
            }
        }
    }

    
    /**
     * Boot do modelo
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
            
            if ($shipment->isDirty('status') && $shipment->status === 'completo') {
                // $shipment->incomplete_reason = null;
            }
        });
        
        // Após atualizar, atualizar requisições relacionadas se o status mudou
        static::updated(function ($shipment) {
            if ($shipment->wasChanged('status')) {
                $shipment->updateRelatedRequests();
            }
        });
        // Atualizar status automaticamente
        static::saved(function ($shipment) {
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