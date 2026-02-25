<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AuditController extends Controller
{
    /**
     * Mostrar página de auditoria
     */
    public function index()
    {
        return view('audit.index');
    }

    /**
     * Dados para DataTable de auditoria
     */
    public function data(Request $request)
    {
        $logs = AuditLog::with('user')
            ->select('audit_logs.*');

        // Filtros
        if ($request->filled('event_type')) {
            $logs->where('event_type', $request->event_type);
        }

        if ($request->filled('user_id')) {
            $logs->where('user_id', $request->user_id);
        }

        if ($request->filled('model_type')) {
            $logs->where('auditable_type', 'like', "%{$request->model_type}%");
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $logs->whereBetween('performed_at', [$dates[0], $dates[1]]);
            }
        }

        if ($request->filled('ip_address')) {
            $logs->where('ip_address', 'like', "%{$request->ip_address}%");
        }

        return DataTables::of($logs)
            ->addIndexColumn()
            ->editColumn('performed_at', function($log) {
                return '<span title="' . $log->performed_at->format('d/m/Y H:i:s') . '">' .
                       $log->performed_at->diffForHumans() . '</span>';
            })
            ->editColumn('user_name', function($log) {
                if ($log->user) {
                    return '<div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    ' . strtoupper(substr($log->user_name, 0, 1)) . '
                                </div>
                                <div>
                                    <div>' . e($log->user_name) . '</div>
                                    <small class="text-muted">' . e($log->user_email) . '</small>
                                </div>
                            </div>';
                }
                return '<span class="text-muted">Sistema</span>';
            })
            ->editColumn('event_type', function($log) {
                $badge = $log->event_badge;
                $icon = $log->event_icon;
                
                return '<span class="badge bg-' . $badge . '">
                            <i class="fas ' . $icon . ' me-1"></i>
                            ' . $log->event_type . '
                        </span>';
            })
            ->editColumn('description', function($log) {
                return '<div class="fw-500">' . $log->formatted_description . '</div>' .
                       '<small class="text-muted">' . class_basename($log->auditable_type) . ' #' . $log->auditable_id . '</small>';
            })
            ->editColumn('ip_address', function($log) {
                return '<div>
                            <span class="badge bg-dark">' . e($log->ip_address) . '</span>
                            <div><small class="text-muted">' . e($log->browser ?? 'N/A') . '</small></div>
                            <div><small class="text-muted">' . e($log->platform ?? 'N/A') . ' - ' . e($log->device ?? 'N/A') . '</small></div>
                        </div>';
            })
            ->addColumn('changes', function($log) {
                if (!$log->changes_formatted) {
                    return '<span class="text-muted">—</span>';
                }
                
                $html = '<button class="btn btn-sm btn-outline-info view-changes" 
                                data-old=\'' . json_encode($log->old_values) . '\'
                                data-new=\'' . json_encode($log->new_values) . '\'
                                title="Ver alterações">
                            <i class="fas fa-code-branch me-1"></i>Ver alterações
                        </button>';
                
                return $html;
            })
            ->rawColumns(['performed_at', 'user_name', 'event_type', 'description', 'ip_address', 'changes'])
            ->make(true);
    }

    /**
     * Estatísticas de auditoria
     */
    public function stats()
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('performed_at', today())->count(),
            'unique_users' => AuditLog::distinct('user_id')->count('user_id'),
            'unique_ips' => AuditLog::distinct('ip_address')->count('ip_address'),
            'events_by_type' => AuditLog::select('event_type', DB::raw('count(*) as total'))
                ->groupBy('event_type')
                ->orderBy('total', 'desc')
                ->get(),
            'recent_activity' => AuditLog::with('user')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($log) {
                    return [
                        'time' => $log->performed_at->diffForHumans(),
                        'user' => $log->user_name,
                        'event' => $log->event_type,
                        'description' => $log->formatted_description
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Detalhes de um log específico
     */
    public function show(AuditLog $auditLog)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'log' => $auditLog->load('user'),
                'changes' => $auditLog->changes_formatted,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values
            ]
        ]);
    }

    /**
     * Exportar logs de auditoria
     */

    /**
 * Exportar logs de auditoria
 */
public function export(Request $request)
{
    try {
        $logs = AuditLog::with('user')
            ->when($request->filled('date_range'), function($query) use ($request) {
                $dates = explode(' - ', $request->date_range);
                if (count($dates) == 2) {
                    $query->whereBetween('performed_at', [
                        Carbon::parse($dates[0])->startOfDay(),
                        Carbon::parse($dates[1])->endOfDay()
                    ]);
                }
            })
            ->orderBy('performed_at', 'desc')
            ->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($logs) {
            $output = fopen('php://output', 'w');
            
            // Adicionar BOM para UTF-8 (para Excel reconhecer acentos)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos em português
            fputcsv($output, [
                'ID',
                'Data/Hora',
                'Utilizador',
                'Email',
                'Tipo de Evento',
                'Descrição',
                'Modelo',
                'ID do Modelo',
                'IP Address',
                'Navegador',
                'Plataforma',
                'Dispositivo',
                'Método HTTP',
                'URL',
                'Session ID'
            ], ';'); // Usar ; como separador para Excel PT

            // Dados
            foreach ($logs as $log) {
                // Parse do user agent
                $agent = null;
                $browser = '-';
                $platform = '-';
                $device = '-';
                
                if ($log->user_agent) {
                    $agent = new \Jenssegers\Agent\Agent();
                    $agent->setUserAgent($log->user_agent);
                    $browser = $agent->browser() ?? '-';
                    $platform = $agent->platform() ?? '-';
                    $device = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');
                }

                fputcsv($output, [
                    $log->id,
                    $log->performed_at ? $log->performed_at->format('d/m/Y H:i:s') : '-',
                    $log->user?->name ?? 'Sistema',
                    $log->user?->email ?? '-',
                    $log->event_type,
                    $log->description,
                    $log->auditable_type ? class_basename($log->auditable_type) : '-',
                    $log->auditable_id ?? '-',
                    $log->ip_address ?? '-',
                    $browser,
                    $platform,
                    $device,
                    $log->request_method ?? '-',
                    $log->request_url ?? '-',
                    $log->session_id ?? '-'
                ], ';');
            }

            fclose($output);
        };

        // Usar response()->stream() em vez de StreamedResponse diretamente
        return response()->stream($callback, 200, $headers);

    } catch (\Exception $e) {
        \Log::error('Erro ao exportar auditoria: ' . $e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erro ao exportar logs: ' . $e->getMessage()
        ], 500);
    }
}
 
}