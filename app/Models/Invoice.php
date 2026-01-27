<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['number', 'date', 'status'];
    
    protected $dates = ['date', 'deleted_at'];
    
    protected $attributes = [
        'status' => 'incompleto',
    ];
    
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
        $this->status = $this->hasDocuments() ? 'completo' : 'incompleto';
        $this->save();
    }
    
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
        
        // Atualizar status automaticamente
        static::saved(function ($invoice) {
            // Se o status não foi definido manualmente, atualiza com base em documentos
            if ($invoice->wasChanged() && !$invoice->wasChanged('status')) {
                $invoice->updateStatus();
            }
        });
    }
}