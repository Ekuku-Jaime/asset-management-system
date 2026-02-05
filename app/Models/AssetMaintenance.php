<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'serial_number', 'brand', 'model', 'barcode',
        'category', 'asset_status', 'process_status', 'base_value', 'iva_value', 'total_value',
        'request_id', 'supplier_id', 'invoice_id', 'shipment_id', 'company_id', 'employee_id', 'project_id',
        'location', 'department', 'purchase_date', 'warranty_expiry', 'last_maintenance',
        'next_maintenance', 'assignment_date'
    ];
    
    protected $dates = [
        'purchase_date', 'warranty_expiry', 'last_maintenance', 
        'next_maintenance', 'assignment_date', 'deleted_at'
    ];
    
    protected $casts = [
        'base_value' => 'decimal:2',
        'iva_value' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];
    
    protected $attributes = [
        'asset_status' => 'disponivel',
        'process_status' => 'incompleto',
        'category' => 'hardware',
    ];
    
    /**
     * Relacionamentos
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Histórico de manutenções
     */
    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }
    
    /**
     * Histórico de atribuições
     */
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }
    
    /**
     * Documentos do ativo
     */
    public function documents()
    {
        return $this->hasMany(AssetDocument::class);
    }
    
    /**
     * Boot do model
     */
    public static function boot()
    {
        parent::boot();
        
        // Gerar código automático
        static::creating(function ($asset) {
            if (!$asset->code) {
                $asset->code = self::generateCode();
            }
            
            if (!$asset->barcode) {
                $asset->barcode = self::generateBarcode();
            }
            
            // Calcular valor total
            $asset->calculateTotalValue();
            
            // Atualizar localização baseada no employee
            $asset->updateLocationFromEmployee();
        });
        
        static::updating(function ($asset) {
            // Calcular valor total
            $asset->calculateTotalValue();
            
            // Atualizar localização se employee mudar
            if ($asset->isDirty('employee_id')) {
                $asset->updateLocationFromEmployee();
            }
            
            // Atualizar process_status baseado em invoice e shipment
            $asset->updateProcessStatus();
        });
        
        static::saved(function ($asset) {
            // Atualizar process_status automaticamente
            $asset->updateProcessStatus();
        });
    }
    
    /**
     * Gerar código automático
     */
    public static function generateCode()
    {
        $lastAsset = self::withTrashed()->latest()->first();
        $number = $lastAsset ? intval(substr($lastAsset->code, 4)) + 1 : 1;
        return 'AST-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Gerar código de barras
     */
    public static function generateBarcode()
    {
        return 'ASSET-' . time() . '-' . Str::random(6);
    }
    
    /**
     * Calcular valor total
     */
    public function calculateTotalValue()
    {
        $this->total_value = $this->base_value + $this->iva_value;
        return $this->total_value;
    }
    
    /**
     * Atualizar localização baseada no employee
     */
    public function updateLocationFromEmployee()
    {
        if ($this->employee && $this->employee->company) {
            $this->location = $this->employee->company->province;
        }
        return $this->location;
    }
    
    /**
     * Atualizar status do processo
     */
    public function updateProcessStatus()
    {
        $isInvoiceComplete = !$this->invoice_id || $this->invoice->status === 'completo';
        $isShipmentComplete = !$this->shipment_id || $this->shipment->status === 'completo';
        
        $this->process_status = ($isInvoiceComplete && $isShipmentComplete) 
            ? 'completo' 
            : 'incompleto';
            
        // Salvar apenas se mudou
        if ($this->isDirty('process_status')) {
            $this->saveQuietly();
        }
    }
    
    /**
     * Verificar se o processo está completo
     */
    public function isProcessComplete()
    {
        return $this->process_status === 'completo';
    }
    
    /**
     * Atribuir ativo a um employee
     */
    public function assignToEmployee($employeeId, $assignmentDate = null)
    {
        $this->employee_id = $employeeId;
        $this->asset_status = 'atribuido';
        $this->assignment_date = $assignmentDate ?: now();
        $this->save();
        
        // Criar registro no histórico
        AssetAssignment::create([
            'asset_id' => $this->id,
            'employee_id' => $employeeId,
            'assignment_date' => $this->assignment_date,
            'status' => 'atribuido'
        ]);
        
        return $this;
    }
    
    /**
     * Liberar ativo (tornar disponível)
     */
    public function releaseAsset($releaseDate = null)
    {
        $this->employee_id = null;
        $this->asset_status = 'disponivel';
        $this->assignment_date = null;
        $this->save();
        
        // Atualizar último assignment
        $lastAssignment = $this->assignments()->latest()->first();
        if ($lastAssignment) {
            $lastAssignment->update([
                'release_date' => $releaseDate ?: now(),
                'status' => 'liberado'
            ]);
        }
        
        return $this;
    }
    
    /**
     * Enviar para manutenção
     */
    public function sendToMaintenance($maintenanceDate = null, $expectedReturn = null)
    {
        $this->asset_status = 'manutencao';
        $this->save();
        
        // Criar registro de manutenção
        AssetMaintenance::create([
            'asset_id' => $this->id,
            'maintenance_date' => $maintenanceDate ?: now(),
            'expected_return' => $expectedReturn,
            'status' => 'em_andamento'
        ]);
        
        return $this;
    }
    
    /**
     * Completar manutenção
     */
    public function completeMaintenance($returnDate = null, $cost = null, $description = null)
    {
        $this->asset_status = 'disponivel';
        $this->last_maintenance = $returnDate ?: now();
        $this->save();
        
        // Atualizar última manutenção
        $lastMaintenance = $this->maintenances()->latest()->first();
        if ($lastMaintenance) {
            $lastMaintenance->update([
                'return_date' => $returnDate ?: now(),
                'cost' => $cost,
                'description' => $description,
                'status' => 'completado'
            ]);
        }
        
        return $this;
    }
    
    /**
     * Marcar como inoperacional
     */
    public function markAsInoperational($reason = null)
    {
        $this->asset_status = 'inoperacional';
        $this->save();
        
        // Criar registro
        AssetMaintenance::create([
            'asset_id' => $this->id,
            'maintenance_date' => now(),
            'description' => $reason ?: 'Marcado como inoperacional',
            'status' => 'inoperacional'
        ]);
        
        return $this;
    }
    
    /**
     * Abater ativo
     */
    public function writeOff($writeOffDate = null, $reason = null)
    {
        $this->asset_status = 'abatido';
        $this->save();
        
        // Criar registro
        AssetMaintenance::create([
            'asset_id' => $this->id,
            'maintenance_date' => $writeOffDate ?: now(),
            'description' => $reason ?: 'Ativo abatido',
            'status' => 'abatido'
        ]);
        
        return $this;
    }
    
    /**
     * Accessors
     */
    public function getAssetStatusLabelAttribute()
    {
        $labels = [
            'disponivel' => 'Disponível',
            'atribuido' => 'Atribuído',
            'manutencao' => 'Em Manutenção',
            'inoperacional' => 'Inoperacional',
            'abatido' => 'Abatido'
        ];
        
        return $labels[$this->asset_status] ?? $this->asset_status;
    }
    
    public function getProcessStatusLabelAttribute()
    {
        return $this->process_status === 'completo' ? 'Completo' : 'Incompleto';
    }
    
    public function getCategoryLabelAttribute()
    {
        $labels = [
            'hardware' => 'Hardware',
            'software' => 'Software',
            'equipamento' => 'Equipamento',
            'mobiliario' => 'Mobiliário',
            'veiculo' => 'Veículo',
            'outro' => 'Outro'
        ];
        
        return $labels[$this->category] ?? $this->category;
    }
    
    public function getFormattedBaseValueAttribute()
    {
        return number_format($this->base_value, 2, ',', '.') . ' MT';
    }
    
    public function getFormattedIvaValueAttribute()
    {
        return number_format($this->iva_value, 2, ',', '.') . ' MT';
    }
    
    public function getFormattedTotalValueAttribute()
    {
        return number_format($this->total_value, 2, ',', '.') . ' MT';
    }
    
    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('asset_status', 'disponivel');
    }
    
    public function scopeAssigned($query)
    {
        return $query->where('asset_status', 'atribuido');
    }
    
    public function scopeInMaintenance($query)
    {
        return $query->where('asset_status', 'manutencao');
    }
    
    public function scopeInoperational($query)
    {
        return $query->where('asset_status', 'inoperacional');
    }
    
    public function scopeWrittenOff($query)
    {
        return $query->where('asset_status', 'abatido');
    }
    
    public function scopeProcessComplete($query)
    {
        return $query->where('process_status', 'completo');
    }
    
    public function scopeProcessIncomplete($query)
    {
        return $query->where('process_status', 'incompleto');
    }
    
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }
    
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
    
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
    
    /**
     * Verificar se tem warranty válida
     */
    public function hasValidWarranty()
    {
        if (!$this->warranty_expiry) {
            return false;
        }
        
        return $this->warranty_expiry > now();
    }
    
    /**
     * Verificar se precisa de manutenção
     */
    public function needsMaintenance()
    {
        if (!$this->next_maintenance) {
            return false;
        }
        
        return $this->next_maintenance <= now()->addDays(30);
    }
    
    /**
     * Verificar se está atribuído
     */
    public function isAssigned()
    {
        return $this->asset_status === 'atribuido' && $this->employee_id !== null;
    }
    
    /**
     * Obter employee atual
     */
    public function getCurrentAssignment()
    {
        return $this->assignments()->latest()->first();
    }
    
    /**
     * Obter manutenção atual
     */
    public function getCurrentMaintenance()
    {
        return $this->maintenances()->whereIn('status', ['em_andamento', 'agendado'])->latest()->first();
    }
}