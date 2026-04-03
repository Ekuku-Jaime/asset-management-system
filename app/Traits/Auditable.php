<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the auditable trait
     */
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->auditCreate();
        });

        static::updating(function (Model $model) {
            $model->auditUpdate();
        });

        static::deleted(function (Model $model) {
            $model->auditDelete();
        });

        // static::restored(function (Model $model) {
        //     $model->auditRestore();
        // });
    }

    /**
     * Audit create event
     */
    protected function auditCreate(): void
    {
        $this->getAuditService()->logCreate($this);
    }

    /**
     * Audit update event
     */
    protected function auditUpdate(): void
    {
        if (!$this->isDirty()) {
            return;
        }

        $oldValues = $this->getOriginal();
        $this->getAuditService()->logUpdate($this, $oldValues);
    }

    /**
     * Audit delete event
     */
    protected function auditDelete(): void
    {
        $this->getAuditService()->logDelete($this);
    }

    /**
     * Audit restore event
     */
    protected function auditRestore(): void
    {
        $this->getAuditService()->logRestore($this);
    }

    /**
     * Get audit service instance
     */
    protected function getAuditService(): AuditService
    {
        return app(AuditService::class);
    }

    /**
     * Get all audit logs for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(\App\Models\AuditLog::class, 'auditable');
    }

    /**
     * Get audit description (can be overridden in models)
     */
    public function getAuditDescription(string $action): string
    {
        $identifier = $this->name ?? $this->code ?? $this->id;
        $modelName = class_basename($this);
        
        return "{$modelName} {$identifier} foi {$action}";
    }
}