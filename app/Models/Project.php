<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'total_value'
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'deleted_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_value' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => 'ativo',
        'total_value' => 0
    ];

    /**
     * Relacionamento com Requests
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Get assets through requests
     */
    public function assets()
    {
        return $this->hasManyThrough(
            Asset::class,
            Request::class,
            'project_id',
            'request_id',
            'id',
            'id'
        );
    }

    /**
     * Gerar código automático
     */
    public static function generateCode()
    {
        $lastProject = self::withTrashed()->orderBy('id', 'desc')->first();
        
        if (!$lastProject) {
            return 'PRJ-0001';
        }

        $lastCode = $lastProject->code;
        $number = intval(substr($lastCode, 4));
        $newNumber = $number + 1;
        
        return 'PRJ-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular progresso do projeto
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        $total = $this->start_date->diffInDays($this->end_date);
        $elapsed = $this->start_date->diffInDays(now());
        
        if ($total <= 0) return 100;
        if ($elapsed >= $total) return 100;
        if ($elapsed <= 0) return 0;
        
        $progress = ($elapsed / $total) * 100;
        return round(min(max($progress, 0), 100), 1);
    }

    /**
     * Valor total dos ativos do projeto
     */
    public function getTotalAssetsValueAttribute()
    {
        return $this->assets()->sum('total_value') ?? 0;
    }

    /**
     * Contagem de ativos do projeto
     */
    public function getAssetsCountAttribute()
    {
        return $this->assets()->count();
    }

    /**
     * Contagem de requisições do projeto
     */
    public function getRequestsCountAttribute()
    {
        return $this->requests()->count();
    }

    /**
     * Status do orçamento
     */
    public function getBudgetStatusAttribute()
    {
        if (!$this->total_value || $this->total_value <= 0) {
            return [
                'class' => 'secondary',
                'label' => 'Sem orçamento',
                'percentage' => 0
            ];
        }
        
        $spent = $this->total_assets_value;
        $percentage = $this->total_value > 0 ? round(($spent / $this->total_value) * 100, 1) : 0;
        
        if ($percentage >= 100) {
            return ['class' => 'danger', 'label' => 'Excedido', 'percentage' => $percentage];
        } elseif ($percentage >= 80) {
            return ['class' => 'warning', 'label' => 'Crítico', 'percentage' => $percentage];
        } elseif ($percentage >= 50) {
            return ['class' => 'info', 'label' => 'Atenção', 'percentage' => $percentage];
        } else {
            return ['class' => 'success', 'label' => 'OK', 'percentage' => $percentage];
        }
    }

    /**
     * Dias restantes para conclusão
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        
        if ($this->status === 'concluido') {
            return 0;
        }
        
        $today = now()->startOfDay();
        $endDate = $this->end_date->startOfDay();
        
        if ($today > $endDate) {
            return -$today->diffInDays($endDate);
        }
        
        return $today->diffInDays($endDate);
    }

    /**
     * Verificar se está atrasado
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'concluido' || !$this->end_date) {
            return false;
        }
        
        return now()->startOfDay() > $this->end_date->startOfDay();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspenso');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopeNotCompleted($query)
    {
        return $query->whereIn('status', ['ativo', 'suspenso']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'concluido')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now());
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->code)) {
                $project->code = self::generateCode();
            }
        });
    }
}