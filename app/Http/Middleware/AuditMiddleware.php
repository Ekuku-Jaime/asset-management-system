<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AuditMiddleware
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Registrar acesso a rotas sensíveis (apenas em produção? podes ajustar)
        if (app()->environment('production') || $request->has('debug')) {
            $this->logSensitiveAccess($request, $response);
        }

        return $response;
    }

    /**
     * Log access to sensitive routes
     */
    protected function logSensitiveAccess(Request $request, $response): void
    {
        // Rotas sensíveis para auditoria
        $sensitiveRoutes = [
            'login', 'logout', 'password', 'profile', 
            'settings', 'admin', 'export', 'import',
            'delete', 'destroy', 'force', 'restore',
            'audit', 'logs', 'backup'
        ];

        $currentRoute = $request->route()?->getName() ?? $request->path();
        
        // Verificar se é uma rota sensível
        $isSensitive = false;
        foreach ($sensitiveRoutes as $route) {
            if (str_contains(strtolower($currentRoute), strtolower($route))) {
                $isSensitive = true;
                break;
            }
        }

        // Também registar todos os métodos POST, PUT, DELETE (modificações)
        $modificationMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (in_array($request->method(), $modificationMethods)) {
            $isSensitive = true;
        }

        if (!$isSensitive) {
            return;
        }

        // Obter status code de forma segura
        $statusCode = $this->getStatusCode($response);

        // Preparar metadata
        $metadata = [
            'route' => $currentRoute,
            'method' => $request->method(),
            'response_status' => $statusCode,
            'ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
        ];

        // Adicionar ID do recurso se for uma rota de recurso
        if ($request->route('id') || $request->route('resource')) {
            $metadata['resource_id'] = $request->route('id') ?? $request->route('resource');
        }

        // Adicionar parâmetros da query (excluindo senhas)
        $queryParams = $request->except(['password', 'password_confirmation', 'token']);
        if (!empty($queryParams)) {
            $metadata['query_params'] = $queryParams;
        }

        // Log do acesso
        $this->auditService->log(
            'ACCESS',
            "Acedeu a: {$currentRoute}",
            null,
            null,
            null,
            $metadata
        );
    }

    /**
     * Get status code safely from different response types
     */
    protected function getStatusCode($response): ?int
    {
        // Se for null ou não for objeto
        if ($response === null) {
            return null;
        }

        // Response com método getStatusCode() (JsonResponse, etc)
        if (method_exists($response, 'getStatusCode')) {
            return $response->getStatusCode();
        }

        // Response com método status()
        if (method_exists($response, 'status')) {
            return $response->status();
        }

        // Response HTTP foundation
        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            return $response->getStatusCode();
        }

        // StreamedResponse
        if ($response instanceof StreamedResponse) {
            // StreamedResponse normalmente retorna 200 se não houver erros
            return 200;
        }

        // BinaryFileResponse
        if ($response instanceof BinaryFileResponse) {
            return 200;
        }

        // RedirectResponse
        if ($response instanceof RedirectResponse) {
            return $response->getStatusCode();
        }

        // Se for uma view ou outro tipo
        if (method_exists($response, 'getData')) {
            return 200;
        }

        return null;
    }

    /**
     * Handle tasks after response has been sent
     */
    public function terminate(Request $request, $response): void
    {
        // Podes fazer logging adicional aqui se necessário
        // Este método é chamado depois da resposta ser enviada ao browser
    }
}