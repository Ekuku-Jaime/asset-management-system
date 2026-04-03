<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Request extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'requests';
    
    protected $fillable = [
        'code', 
        'date', 
        'type', 
        'description', 
        'project_id',
        'shipment_id',
        'invoice_id',
        'supplier_id',
        'process_status',
        'incomplete_reason'
    ];
    
    protected $dates = ['date', 'deleted_at'];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    protected $attributes = [
        'process_status' => 'incompleto',
    ];
    
    /**
     * Relacionamentos
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'request_id');
    }
    
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    
    /**
     * Validar que a data não é futura
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if ($request->date > now()) {
                throw new \Exception('A data da requisição não pode ser no futuro.');
            }
            
            if (empty($request->code)) {
                $request->code = self::generateCode();
            }
            
            $request->updateProcessStatus();
        });

        static::updating(function ($request) {
            if ($request->date > now()) {
                throw new \Exception('A data da requisição não pode ser no futuro.');
            }
            
            $request->updateProcessStatus();
        });
        
        // Após salvar, se o status mudou, atualizar ativos relacionados
        static::saved(function ($request) {
            if ($request->wasChanged('process_status')) {
                foreach ($request->assets as $asset) {
                    $asset->updateProcessStatus();
                    $asset->save();
                }
            }
        });
    }
    
    /**
     * Gerar código automático
     */
    public static function generateCode()
    {
        $lastRequest = self::withTrashed()->orderBy('id', 'desc')->first();
        $number = $lastRequest ? intval(substr($lastRequest->code, 4)) + 1 : 1;
        return 'REQ-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Atualizar status do processo baseado nos relacionamentos
     */
    public function updateProcessStatus()
    {
        $missingFields = [];
        
        // Verificar campos obrigatórios básicos
        if (empty($this->code)) $missingFields[] = 'Código';
        if (empty($this->date)) $missingFields[] = 'Data';
        if (empty($this->type)) $missingFields[] = 'Tipo';
        
        // Verificar relacionamentos obrigatórios
        if (!$this->project_id) {
            $missingFields[] = 'Projeto';
        } elseif ($this->project && $this->project->trashed()) {
            $missingFields[] = 'Projeto eliminado';
        }
        
        if (!$this->supplier_id) {
            $missingFields[] = 'Fornecedor';
        } elseif ($this->supplier && $this->supplier->trashed()) {
            $missingFields[] = 'Fornecedor eliminado';
        }
        
        // Verificar fatura
        if (!$this->invoice_id) {
            $missingFields[] = 'Fatura';
        } else {
            // Carregar a fatura se não estiver carregada
            if (!$this->relationLoaded('invoice')) {
                $this->load('invoice');
            }
            
            if ($this->invoice) {
                if ($this->invoice->trashed()) {
                    $missingFields[] = 'Fatura eliminada';
                } elseif ($this->invoice->status === 'incompleto') {
                    $missingFields[] = 'Fatura incompleta: ' . ($this->invoice->incomplete_reason ?? 'Documentos pendentes');
                }
            }
        }
        
        // Verificar remessa
        if (!$this->shipment_id) {
            $missingFields[] = 'Remessa';
        } else {
            // Carregar a remessa se não estiver carregada
            if (!$this->relationLoaded('shipment')) {
                $this->load('shipment');
            }
            
            if ($this->shipment) {
                if ($this->shipment->trashed()) {
                    $missingFields[] = 'Remessa eliminada';
                } elseif ($this->shipment->status === 'incompleto') {
                    $missingFields[] = 'Remessa incompleta: ' . ($this->shipment->incomplete_reason ?? 'Documentos pendentes');
                }
            }
        }
        
        // Determinar status final
        if (empty($missingFields)) {
            $this->process_status = 'completo';
            $this->incomplete_reason = null;
        } else {
            $this->process_status = 'incompleto';
            $this->incomplete_reason = 'Faltam: ' . implode('; ', $missingFields);
        }
        
        return $this;
    }
    
    /**
     * Verificar se a requisição está completa
     */
    public function isComplete()
    {
        return $this->process_status === 'completo';
    }
    
    /**
     * Escopos
     */
    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }
    
    public function scopeExternal($query)
    {
        return $query->where('type', 'external');
    }
    
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }
    
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
    
    public function scopeProcessComplete($query)
    {
        return $query->where('process_status', 'completo');
    }
    
    public function scopeProcessIncomplete($query)
    {
        return $query->where('process_status', 'incompleto');
    }
    
    public function scopeWithShipment($query)
    {
        return $query->whereNotNull('shipment_id');
    }
    
    public function scopeWithoutShipment($query)
    {
        return $query->whereNull('shipment_id');
    }
    
    public function scopeWithInvoice($query)
    {
        return $query->whereNotNull('invoice_id');
    }
    
    public function scopeWithoutInvoice($query)
    {
        return $query->whereNull('invoice_id');
    }
    
    public function scopeWithSupplier($query)
    {
        return $query->whereNotNull('supplier_id');
    }
    
    public function scopeWithoutSupplier($query)
    {
        return $query->whereNull('supplier_id');
    }

    /**
     * Accessors
     */
    public function getFormattedCodeAttribute()
    {
        return $this->code;
    }
    
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('d/m/Y') : '-';
    }
    
    public function getTypeLabelAttribute()
    {
        $labels = [
            'internal' => 'Interna',
            'external' => 'Externa'
        ];
        
        return $labels[$this->type] ?? $this->type;
    }
    
    public function getProcessStatusLabelAttribute()
    {
        $labels = [
            'completo' => 'Completo',
            'incompleto' => 'Incompleto'
        ];
        
        return $labels[$this->process_status] ?? $this->process_status;
    }
    
    /**
     * Verificar se tem ativos associados
     */
    public function hasAssets()
    {
        return $this->assets()->count() > 0;
    }
    
    /**
     * Contar ativos associados
     */
    public function getAssetsCountAttribute()
    {
        return $this->assets()->count();
    }
    
    /**
     * Valor total dos ativos da requisição
     */
    public function getTotalAssetsValueAttribute()
    {
        return $this->assets()->sum('total_value');
    }
    
    /**
     * Status de documentação
     */
    public function getDocumentationStatusAttribute()
    {
        $total = 4; // project, supplier, invoice, shipment
        $completed = 0;
        
        if ($this->project_id && $this->project && !$this->project->trashed()) $completed++;
        if ($this->supplier_id && $this->supplier && !$this->supplier->trashed()) $completed++;
        if ($this->invoice_id && $this->invoice && !$this->invoice->trashed() && $this->invoice->status === 'completo') $completed++;
        if ($this->shipment_id && $this->shipment && !$this->shipment->trashed() && $this->shipment->status === 'completo') $completed++;
        
        $percentage = ($completed / $total) * 100;
        
        if ($percentage >= 100) {
            return ['class' => 'success', 'label' => 'Completo', 'percentage' => 100];
        } elseif ($percentage >= 75) {
            return ['class' => 'info', 'label' => 'Quase completo', 'percentage' => $percentage];
        } elseif ($percentage >= 50) {
            return ['class' => 'warning', 'label' => 'Parcial', 'percentage' => $percentage];
        } else {
            return ['class' => 'danger', 'label' => 'Incompleto', 'percentage' => $percentage];
        }
    }
}