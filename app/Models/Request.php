<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'requests';
    
    protected $fillable = ['code', 'date', 'type', 'description', 'project_id'];
    
    protected $dates = ['date', 'deleted_at'];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    /**
     * Relacionamento com Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
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
        });

        static::updating(function ($request) {
            if ($request->date > now()) {
                throw new \Exception('A data da requisição não pode ser no futuro.');
            }
        });
    }
    
    /**
     * Gerar código automático
     */
    public static function generateCode()
    {
        $lastRequest = self::latest()->first();
        $number = $lastRequest ? intval(substr($lastRequest->code, 4)) + 1 : 1;
        return 'REQ-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Escopo para requisições internas
     */
    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }
    
    /**
     * Escopo para requisições externas
     */
    public function scopeExternal($query)
    {
        return $query->where('type', 'external');
    }
    
    /**
     * Escopo para requisições recentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }
    
    /**
     * Escopo para requisições de um projeto específico
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}