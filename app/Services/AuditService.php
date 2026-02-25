<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class AuditService
{
    protected $request;
    protected $agent;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
        $this->agent = new Agent();
        $this->agent->setUserAgent($this->request->userAgent() ?? '');
    }

    /**
     * Registrar um evento de auditoria
     */
    public function log(
        string $eventType,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = []
    ): AuditLog {
        $user = Auth::user();
        
        $data = [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->role ?? 'N/A',
            'performed_at' => now(),
            'event_type' => $eventType,
            'description' => $description,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $this->request->userAgent(),
            'browser' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'device' => $this->getDeviceType(),
            'request_method' => $this->request->method(),
            'request_url' => $this->request->fullUrl(),
            'session_id' => session()->getId(),
        ];

        if ($auditable) {
            $data['auditable_type'] = get_class($auditable);
            $data['auditable_id'] = $auditable->id;
        }

        if ($oldValues) {
            $data['old_values'] = $this->sanitizeValues($oldValues);
        }

        if ($newValues) {
            $data['new_values'] = $this->sanitizeValues($newValues);
        }

        if (!empty($metadata)) {
            $data = array_merge($data, $metadata);
        }

        return AuditLog::create($data);
    }

    /**
     * Registrar criação de modelo
     */
    public function logCreate(Model $model, string $description = null): AuditLog
    {
        $description = $description ?? $this->getDefaultDescription('criado', $model);
        
        return $this->log(
            'CREATE',
            $description,
            $model,
            null,
            $model->toArray()
        );
    }

    /**
     * Registrar atualização de modelo
     */
    public function logUpdate(Model $model, array $oldValues, string $description = null): AuditLog
    {
        $description = $description ?? $this->getDefaultDescription('atualizado', $model);
        
        return $this->log(
            'UPDATE',
            $description,
            $model,
            $oldValues,
            $model->toArray()
        );
    }

    /**
     * Registrar eliminação de modelo
     */
    public function logDelete(Model $model, string $description = null): AuditLog
    {
        $description = $description ?? $this->getDefaultDescription('eliminado', $model);
        
        return $this->log(
            'DELETE',
            $description,
            $model,
            $model->toArray(),
            null
        );
    }

    /**
     * Registrar restauração de modelo
     */
    public function logRestore(Model $model, string $description = null): AuditLog
    {
        $description = $description ?? $this->getDefaultDescription('restaurado', $model);
        
        return $this->log(
            'RESTORE',
            $description,
            $model,
            null,
            $model->toArray()
        );
    }

    /**
     * Registrar login
     */
    public function logLogin(): AuditLog
    {
        $user = Auth::user();
        
        return $this->log(
            'LOGIN',
            "Utilizador {$user?->name} iniciou sessão",
            $user
        );
    }

    /**
     * Registrar logout
     */
    public function logLogout(): AuditLog
    {
        $user = Auth::user();
        
        return $this->log(
            'LOGOUT',
            "Utilizador {$user?->name} terminou sessão",
            $user
        );
    }

    /**
     * Registrar exportação de dados
     */
    public function logExport(string $type, array $filters = []): AuditLog
    {
        $user = Auth::user();
        $filterDesc = !empty($filters) ? ' com filtros: ' . json_encode($filters) : '';
        
        return $this->log(
            'EXPORT',
            "Exportou {$type}{$filterDesc}",
            null,
            null,
            null,
            ['export_type' => $type, 'filters' => $filters]
        );
    }

    /**
     * Registrar importação de dados
     */
    public function logImport(string $type, int $count): AuditLog
    {
        return $this->log(
            'IMPORT',
            "Importou {$count} registos de {$type}",
            null,
            null,
            null,
            ['import_type' => $type, 'record_count' => $count]
        );
    }

    /**
     * Registrar atribuição de ativo
     */
    public function logAssignment(string $assetCode, string $employeeName): AuditLog
    {
        return $this->log(
            'ASSIGN',
            "Ativo {$assetCode} atribuído a {$employeeName}",
            null,
            null,
            null,
            ['asset_code' => $assetCode, 'employee_name' => $employeeName]
        );
    }

    /**
     * Registrar remoção de atribuição
     */
    public function logUnassignment(string $assetCode, string $employeeName): AuditLog
    {
        return $this->log(
            'UNASSIGN',
            "Ativo {$assetCode} removido de {$employeeName}",
            null,
            null,
            null,
            ['asset_code' => $assetCode, 'employee_name' => $employeeName]
        );
    }

    /**
     * Obter descrição padrão para um modelo
     */
    protected function getDefaultDescription(string $action, Model $model): string
    {
        $modelName = class_basename($model);
        
        if (method_exists($model, 'getAuditDescription')) {
            return $model->getAuditDescription($action);
        }

        $identifier = $model->name ?? $model->code ?? $model->id;
        
        return "{$modelName} {$identifier} foi {$action}";
    }

    /**
     * Sanitizar valores para evitar dados sensíveis
     */
    protected function sanitizeValues(array $values): array
    {
        $sensitiveFields = ['password', 'remember_token', 'api_token', 'credit_card', 'nuit'];
        
        foreach ($values as $key => $value) {
            if (in_array($key, $sensitiveFields)) {
                $values[$key] = '[REDACTED]';
            }
        }
        
        return $values;
    }

    /**
     * Obter IP do cliente (considerando proxy)
     */
    protected function getClientIp(): ?string
    {
        $ips = [
            $this->request->header('HTTP_CF_CONNECTING_IP'), // Cloudflare
            $this->request->header('HTTP_X_FORWARDED_FOR'),
            $this->request->header('HTTP_X_REAL_IP'),
            $this->request->header('HTTP_CLIENT_IP'),
            $this->request->getClientIp()
        ];

        foreach ($ips as $ip) {
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return $this->request->ip();
    }

    /**
     * Obter tipo de dispositivo
     */
    protected function getDeviceType(): string
    {
        if ($this->agent->isMobile()) {
            return 'Mobile';
        } elseif ($this->agent->isTablet()) {
            return 'Tablet';
        } elseif ($this->agent->isDesktop()) {
            return 'Desktop';
        } elseif ($this->agent->isRobot()) {
            return 'Robot';
        }
        
        return 'Unknown';
    }
}