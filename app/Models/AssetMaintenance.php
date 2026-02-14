<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'description',
        'status',
        'scheduled_date',
        'completed_date',
        'started_date',
        'estimated_duration',
        'actual_duration',
        'cost',
        'maintenance_provider',
        'technician_name',
        'result',
        'notes'
    ];
    
    protected $dates = [
        'scheduled_date',
        'completed_date',
        'started_date',
        'deleted_at'
    ];
    
    protected $attributes = [
        'status' => 'agendada'
    ];
    
    /**
     * Relacionamento com Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    
    /**
     * Accessors
     */
    public function getMaintenanceTypeLabelAttribute()
    {
        $labels = [
            'preventiva' => 'Preventiva',
            'corretiva' => 'Corretiva',
            'preditiva' => 'Preditiva'
        ];
        
        return $labels[$this->maintenance_type] ?? $this->maintenance_type;
    }
    
    public function getStatusLabelAttribute()
    {
        $labels = [
            'agendada' => 'Agendada',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    public function getResultLabelAttribute()
    {
        if (!$this->result) {
            return null;
        }
        
        $labels = [
            'concluida' => 'Concluída',
            'pendente' => 'Pendente',
            'cancelada' => 'Cancelada'
        ];
        
        return $labels[$this->result] ?? $this->result;
    }
    
    public function getFormattedCostAttribute()
    {
        return $this->cost ? number_format($this->cost, 2, ',', '.') . ' MT' : null;
    }
    
    public function getIsOverdueAttribute()
    {
        return $this->status !== 'concluida' && 
               $this->scheduled_date && 
               $this->scheduled_date->isPast();
    }
    
    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        
        return $this->scheduled_date->diffInDays(now());
    }
    
    /**
     * Scopes
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'agendada');
    }
    
    public function scopeInProgress($query)
    {
        return $query->where('status', 'em_andamento');
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'concluida');
    }
    
    public function scopeCanceled($query)
    {
        return $query->where('status', 'cancelada');
    }
    
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'concluida')
            ->whereDate('scheduled_date', '<', now());
    }
    
    public function scopePreventive($query)
    {
        return $query->where('maintenance_type', 'preventiva');
    }
    
    public function scopeCorrective($query)
    {
        return $query->where('maintenance_type', 'corretiva');
    }
    
    public function scopePredictive($query)
    {
        return $query->where('maintenance_type', 'preditiva');
    }
    
    public function scopeForAsset($query, $assetId)
    {
        return $query->where('asset_id', $assetId);
    }
    
    /**
     * Calcular diferença entre duração estimada e real
     */
    public function getDurationDifferenceAttribute()
    {
        if (!$this->estimated_duration || !$this->actual_duration) {
            return null;
        }
        
        return $this->actual_duration - $this->estimated_duration;
    }
    
    /**
     * Verificar se está dentro do prazo
     */
    public function getIsOnTimeAttribute()
    {
        if (!$this->actual_duration || !$this->estimated_duration) {
            return null;
        }
        
        return $this->actual_duration <= $this->estimated_duration;
    }
}