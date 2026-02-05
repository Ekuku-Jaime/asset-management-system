<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'employee_id', 'assignment_date', 'release_date',
        'status', 'notes'
    ];
    
    protected $dates = ['assignment_date', 'release_date'];
    
    /**
     * Relacionamentos
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'atribuido' => 'Atribuído',
            'liberado' => 'Liberado',
            'transferido' => 'Transferido'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    /**
     * Verificar se está ativo
     */
    public function isActive()
    {
        return $this->status === 'atribuido' && !$this->release_date;
    }
    
    /**
     * Calcular duração da atribuição
     */
    public function getDurationAttribute()
    {
        if (!$this->assignment_date) {
            return null;
        }
        
        $endDate = $this->release_date ?: now();
        return $this->assignment_date->diff($endDate);
    }
    
    /**
     * Obter duração formatada
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return '-';
        }
        
        $duration = $this->duration;
        
        if ($duration->y > 0) {
            return $duration->y . ' ano' . ($duration->y > 1 ? 's' : '');
        } elseif ($duration->m > 0) {
            return $duration->m . ' mês' . ($duration->m > 1 ? 'es' : '');
        } elseif ($duration->d > 0) {
            return $duration->d . ' dia' . ($duration->d > 1 ? 's' : '');
        } else {
            return 'Menos de 1 dia';
        }
    }
}