<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = ['number', 'date', 'status'];
    
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
            'invoice_id', // Foreign key on requests table
            'request_id',  // Foreign key on assets table
            'id',          // Local key on invoices table
            'id'           // Local key on requests table
        );
    }
    
    /**
     * Relacionamento com documentos
     */
    public function documents()
    {
        return $this->hasMany(InvoiceDocument::class);
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
            
            // Atualizar todas as requisições relacionadas a esta fatura
            $this->updateRelatedRequests();
        } else {
            $this->saveQuietly();
        }
    }
    
    /**
     * Atualizar todas as requisições relacionadas a esta fatura
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

        static::creating(function ($invoice) {
            if ($invoice->date > now()) {
                throw new \Exception('A data da fatura não pode ser no futuro.');
            }
        });

        static::updating(function ($invoice) {
            if ($invoice->date > now()) {
                throw new \Exception('A data da fatura não pode ser no futuro.');
            }
            
            if ($invoice->isDirty('status') && $invoice->status === 'completo') {
                // $invoice->incomplete_reason = null;
            }
        });
        
        static::updated(function ($invoice) {
            if ($invoice->wasChanged('status')) {
                $invoice->updateRelatedRequests();
            }
        });
        // Atualizar status automaticamente
        static::saved(function ($invoice) {
            if ($invoice->wasChanged() && !$invoice->wasChanged('status')) {
                $invoice->updateStatus();
            }
        });
    }
}