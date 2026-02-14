<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'serial_number', 'brand', 'model',
        'category', 'asset_status', 'process_status', 'incomplete_reason',
        'total_value', 'base_value', 'iva_value',
        'request_id', 'supplier_id', 'invoice_id', 'shipment_id',
        'employee_id', 'location', 'department', 'warranty_expiry',
        'last_maintenance', 'next_maintenance', 'assignment_date'
    ];
    
    protected $dates = [
        'warranty_expiry', 'last_maintenance', 
        'next_maintenance', 'assignment_date', 'deleted_at'
    ];
    
    protected $casts = [
        'total_value' => 'decimal:2',
        'base_value' => 'decimal:2',
        'iva_value' => 'decimal:2',
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
        return $this->belongsTo(Request::class, 'request_id');
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    public function project()
    {
        return $this->hasOneThrough(
            Project::class,
            Request::class,
            'id', // Foreign key on requests table
            'id', // Foreign key on projects table
            'request_id', // Local key on assets table
            'project_id' // Local key on requests table
        );
    }
    
    public function company()
    {
        return $this->hasOneThrough(
            Company::class,
            Employee::class,
            'id', // Foreign key on employees table
            'id', // Foreign key on companies table
            'employee_id', // Local key on assets table
            'company_id' // Local key on employees table
        );
    }
    
    /**
     * Histórico de manutenções
     */
    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }
    
    /**
     * Histórico de atribuições
     */
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }
    
    /**
     * Documentos do ativo
     */
    public function documents()
    {
        return $this->hasMany(AssetDocument::class, 'asset_id');
    }
    
    /**
     * Boot do model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Calcular IVA automaticamente (16%)
        static::creating(function ($asset) {
            $asset->calculateIVA();
        });
        
        static::updating(function ($asset) {
            $asset->calculateIVA();
        });
    }
    
    /**
     * Calcular IVA automaticamente (16%)
     */
    public function calculateIVA()
    {
        if ($this->total_value) {
            $this->base_value = $this->total_value / 1.16;
            $this->iva_value = $this->total_value - $this->base_value;
            
            // Arredondar para 2 casas decimais
            $this->base_value = round($this->base_value, 2);
            $this->iva_value = round($this->iva_value, 2);
        }
        
        return $this;
    }
    
    /**
     * Atribuir ativo a um employee
     */
    public function assignToEmployee($employeeId, $assignmentDate = null)
    {
        $employee = Employee::find($employeeId);
        
        if ($employee) {
            $this->employee_id = $employeeId;
            $this->asset_status = 'atribuido';
            $this->assignment_date = $assignmentDate ?: now();
            $this->location = $employee->company ? $employee->company->province : null;
            $this->save();
            
            // Criar registro no histórico
            AssetAssignment::create([
                'asset_id' => $this->id,
                'employee_id' => $employeeId,
                'assignment_date' => $this->assignment_date,
                'status' => 'atribuido'
            ]);
        }
        
        return $this;
    }
    
    /**
     * Remover atribuição
     */
    public function removeAssignment()
    {
        $this->employee_id = null;
        $this->asset_status = 'disponivel';
        $this->assignment_date = null;
        $this->location = null;
        $this->save();
        
        // Atualizar último assignment
        $lastAssignment = $this->assignments()->latest()->first();
        if ($lastAssignment) {
            $lastAssignment->update([
                'release_date' => now(),
                'status' => 'liberado'
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
        
        AssetMaintenance::create([
            'asset_id' => $this->id,
            'maintenance_date' => now(),
            'description' => $reason ?: 'Marcado como inoperacional',
            'status' => 'inoperacional'
        ]);
        
        return $this;
    }
    
    /**
     * Mudar status de inoperacional
     */
    public function changeFromInoperational($newStatus, $reason = null)
    {
        if ($this->asset_status !== 'inoperacional') {
            throw new \Exception('Só é possível mudar de inoperacional');
        }
        
        $this->asset_status = $newStatus;
        $this->save();
        
        AssetMaintenance::create([
            'asset_id' => $this->id,
            'maintenance_date' => now(),
            'description' => $reason ?: "Mudado de inoperacional para {$newStatus}",
            'status' => $newStatus
        ]);
        
        return $this;
    }
    
    /**
     * Abater ativo
     */
    public function writeOff($reason = null)
    {
        $this->asset_status = 'abatido';
        $this->save();
        
        AssetMaintenance::create([
            'asset_id' => $this->id,
            'maintenance_date' => now(),
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
        $labels = [
            'completo' => 'Completo',
            'incompleto' => 'Incompleto'
        ];
        
        return $labels[$this->process_status] ?? $this->process_status;
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
    
    public function getFormattedTotalValueAttribute()
    {
        return number_format($this->total_value, 2, ',', '.') . ' MT';
    }
    
    public function getFormattedBaseValueAttribute()
    {
        return number_format($this->base_value, 2, ',', '.') . ' MT';
    }
    
    public function getFormattedIvaValueAttribute()
    {
        return number_format($this->iva_value, 2, ',', '.') . ' MT';
    }
    
    public function getFormattedWarrantyExpiryAttribute()
    {
        return $this->warranty_expiry ? $this->warranty_expiry->format('d/m/Y') : '-';
    }
    
    public function getFormattedAssignmentDateAttribute()
    {
        return $this->assignment_date ? $this->assignment_date->format('d/m/Y') : '-';
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
    
    /**
     * Scope para pesquisa avançada
     */
    public function scopeAdvancedSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('code', 'like', "%{$searchTerm}%")
              ->orWhere('name', 'like', "%{$searchTerm}%")
              ->orWhere('serial_number', 'like', "%{$searchTerm}%")
              ->orWhere('brand', 'like', "%{$searchTerm}%")
              ->orWhere('model', 'like', "%{$searchTerm}%")
              ->orWhere('department', 'like', "%{$searchTerm}%")
              ->orWhere('location', 'like', "%{$searchTerm}%")
              ->orWhereHas('request', function($q2) use ($searchTerm) {
                  $q2->where('code', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('invoice', function($q2) use ($searchTerm) {
                  $q2->where('number', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('supplier', function($q2) use ($searchTerm) {
                  $q2->where('name', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('employee', function($q2) use ($searchTerm) {
                  $q2->where('name', 'like', "%{$searchTerm}%");
              });
        });
    }
}