<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'performed_at',
        'event_type',
        'auditable_type',
        'auditable_id',
        'description',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device',
        'old_values',
        'new_values',
        'request_method',
        'request_url',
        'session_id'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relacionamento com o usuário que executou a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento polimórfico com o modelo auditado
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Escopo para filtrar por modelo
     */
    public function scopeForModel($query, string $modelType, $modelId = null)
    {
        $query->where('auditable_type', $modelType);
        
        if ($modelId) {
            $query->where('auditable_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Escopo para filtrar por usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Escopo para filtrar por evento
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event_type', $event);
    }

    /**
     * Escopo para filtrar por período
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Escopo para filtrar por IP
     */
    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Obter descrição formatada do evento
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $modelName = class_basename($this->auditable_type);
        
        switch ($this->event_type) {
            case 'CREATE':
                return "Criou {$modelName}: {$this->description}";
            case 'UPDATE':
                return "Atualizou {$modelName}: {$this->description}";
            case 'DELETE':
                return "Eliminou {$modelName}: {$this->description}";
            case 'RESTORE':
                return "Restaurou {$modelName}: {$this->description}";
            case 'LOGIN':
                return "Iniciou sessão";
            case 'LOGOUT':
                return "Terminou sessão";
            case 'EXPORT':
                return "Exportou dados: {$this->description}";
            case 'IMPORT':
                return "Importou dados: {$this->description}";
            case 'ASSIGN':
                return "Atribuiu: {$this->description}";
            case 'UNASSIGN':
                return "Removeu atribuição: {$this->description}";
            default:
                return $this->description;
        }
    }

    /**
     * Obter badge de cor para o tipo de evento
     */
    public function getEventBadgeAttribute(): string
    {
        $badges = [
            'CREATE' => 'success',
            'UPDATE' => 'info',
            'DELETE' => 'danger',
            'RESTORE' => 'warning',
            'LOGIN' => 'primary',
            'LOGOUT' => 'secondary',
            'EXPORT' => 'dark',
            'IMPORT' => 'dark',
            'ASSIGN' => 'success',
            'UNASSIGN' => 'warning'
        ];

        return $badges[$this->event_type] ?? 'secondary';
    }

    /**
     * Obter ícone para o tipo de evento
     */
    public function getEventIconAttribute(): string
    {
        $icons = [
            'CREATE' => 'fa-plus-circle',
            'UPDATE' => 'fa-edit',
            'DELETE' => 'fa-trash',
            'RESTORE' => 'fa-undo',
            'LOGIN' => 'fa-sign-in-alt',
            'LOGOUT' => 'fa-sign-out-alt',
            'EXPORT' => 'fa-download',
            'IMPORT' => 'fa-upload',
            'ASSIGN' => 'fa-user-tag',
            'UNASSIGN' => 'fa-user-minus'
        ];

        return $icons[$this->event_type] ?? 'fa-history';
    }

    /**
     * Formatar alterações para exibição
     */
    public function getChangesFormattedAttribute(): ?array
    {
        if (!$this->old_values || !$this->new_values) {
            return null;
        }

        $changes = [];
        $old = $this->old_values;
        $new = $this->new_values;

        foreach ($new as $key => $value) {
            if (array_key_exists($key, $old) && $old[$key] != $value) {
                $changes[$key] = [
                    'old' => $old[$key],
                    'new' => $value
                ];
            }
        }

        return $changes;
    }
}