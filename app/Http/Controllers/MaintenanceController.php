<?php

namespace App\Http\Controllers;

use App\Models\AssetMaintenance;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MaintenanceController extends Controller
{
    const MAINTENANCE_TYPES = [
        'preventiva' => 'Preventiva',
        'corretiva' => 'Corretiva', 
        'preditiva' => 'Preditiva'
    ];

    const MAINTENANCE_STATUSES = [
        'agendada' => 'Agendada',
        'em_andamento' => 'Em Andamento',
        'concluida' => 'Concluída',
        'cancelada' => 'Cancelada'
    ];

    const MAINTENANCE_RESULTS = [
        'concluida' => 'Concluída',
        'pendente' => 'Pendente',
        'cancelada' => 'Cancelada'
    ];

    /**
     * Display a listing of maintenance records
     */
    public function index()
    {
        $data = [
            'maintenance_types' => self::MAINTENANCE_TYPES,
            'maintenance_statuses' => self::MAINTENANCE_STATUSES,
            'maintenance_results' => self::MAINTENANCE_RESULTS,
            'assets' => Asset::with(['employee', 'employee.company'])->get()
        ];

        return view('maintenances.index', $data);
    }

    /**
     * DataTable data for maintenance records
     */
    public function datatable(Request $request)
    {
        try {
            $query = AssetMaintenance::with([
                'asset',
                'asset.employee.company',
                'asset.supplier'
            ]);

            // Aplicar filtros
            if ($request->filled('quick_filter') && $request->quick_filter !== 'all') {
                $query = $this->applyQuickFilter($query, $request->quick_filter);
            }

            if ($request->filled('status_filter') && $request->status_filter !== 'all') {
                $query->where('status', $request->status_filter);
            }

            if ($request->filled('type_filter') && $request->type_filter !== 'all') {
                $query->where('maintenance_type', $request->type_filter);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('scheduled_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('scheduled_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('description', 'like', "%{$searchTerm}%")
                      ->orWhere('technician_name', 'like', "%{$searchTerm}%")
                      ->orWhere('maintenance_provider', 'like', "%{$searchTerm}%")
                      ->orWhereHas('asset', function($q) use ($searchTerm) {
                          $q->where('code', 'like', "%{$searchTerm}%")
                            ->orWhere('name', 'like', "%{$searchTerm}%")
                            ->orWhere('serial_number', 'like', "%{$searchTerm}%");
                      });
                });
            }

            return DataTables::eloquent($query)
                ->addColumn('checkbox', function($maintenance) {
                    return '<input type="checkbox" class="select-checkbox maintenance-checkbox" data-id="' . $maintenance->id . '">';
                })
                ->addColumn('asset_info', function($maintenance) {
                    if ($maintenance->asset) {
                        return '
                            <div class="d-flex flex-column">
                                <strong class="text-primary">' . e($maintenance->asset->code) . '</strong>
                                <small class="text-muted">' . e($maintenance->asset->name) . '</small>
                            </div>
                        ';
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('type_badge', function($maintenance) {
                    $badgeClass = [
                        'preventiva' => 'badge-preventiva',
                        'corretiva' => 'badge-corretiva',
                        'preditiva' => 'badge-preditiva'
                    ][$maintenance->maintenance_type] ?? 'badge-secondary';
                    
                    return '<span class="badge ' . $badgeClass . '">' . 
                           (self::MAINTENANCE_TYPES[$maintenance->maintenance_type] ?? $maintenance->maintenance_type) . '</span>';
                })
                ->addColumn('status_badge', function($maintenance) {
                    $badgeClass = [
                        'agendada' => 'badge-warning',
                        'em_andamento' => 'badge-info',
                        'concluida' => 'badge-success',
                        'cancelada' => 'badge-danger'
                    ][$maintenance->status] ?? 'badge-secondary';
                    
                    return '<span class="badge ' . $badgeClass . '">' . 
                           (self::MAINTENANCE_STATUSES[$maintenance->status] ?? $maintenance->status) . '</span>';
                })
                ->addColumn('result_badge', function($maintenance) {
                    if (!$maintenance->result) {
                        return '<span class="text-muted">--</span>';
                    }
                    
                    $badgeClass = [
                        'concluida' => 'badge-success',
                        'pendente' => 'badge-warning',
                        'cancelada' => 'badge-danger'
                    ][$maintenance->result] ?? 'badge-secondary';
                    
                    return '<span class="badge ' . $badgeClass . '">' . 
                           (self::MAINTENANCE_RESULTS[$maintenance->result] ?? $maintenance->result) . '</span>';
                })
                ->addColumn('scheduled_date_formatted', function($maintenance) {
                    return $maintenance->scheduled_date ? 
                        $maintenance->scheduled_date : '--';
                })
                ->addColumn('completion_info', function($maintenance) {
                    if ($maintenance->completed_date) {
                        return '
                            <div class="d-flex flex-column">
                                <span>' . $maintenance->completed_date . '</span>
                                <small class="text-muted">' . 
                                    ($maintenance->actual_duration ? $maintenance->actual_duration . ' dias' : '') . 
                                '</small>
                            </div>
                        ';
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('cost_formatted', function($maintenance) {
                    if ($maintenance->cost) {
                        return number_format($maintenance->cost, 2, ',', '.') . ' MT';
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('duration_info', function($maintenance) {
                    $info = [];
                    if ($maintenance->estimated_duration) {
                        $info[] = 'Est: ' . $maintenance->estimated_duration . 'd';
                    }
                    if ($maintenance->actual_duration) {
                        $info[] = 'Real: ' . $maintenance->actual_duration . 'd';
                    }
                    return count($info) > 0 ? implode('<br>', $info) : '--';
                })
                ->addColumn('assigned_to', function($maintenance) {
                    if ($maintenance->asset && $maintenance->asset->employee) {
                        return '
                            <div class="d-flex flex-column">
                                <span>' . e($maintenance->asset->employee->name) . '</span>
                                <small class="text-muted">' . 
                                    ($maintenance->asset->employee->company ? e($maintenance->asset->employee->company->name) : '') . 
                                '</small>
                            </div>
                        ';
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('provider_info', function($maintenance) {
                    if ($maintenance->maintenance_provider) {
                        return e($maintenance->maintenance_provider);
                    }
                    return '<span class="text-muted">--</span>';
                })
                ->addColumn('actions', function($maintenance) {
                    $buttons = '<div class="action-buttons">';
                    
                    // Ver detalhes
                    $buttons .= '<button class="btn-action view" onclick="showMaintenanceDetails(' . $maintenance->id . ')" title="Ver detalhes"><i class="fas fa-eye"></i></button>';
                    
                    // Editar (apenas se não estiver concluída)
                    if ($maintenance->status !== 'concluida' && $maintenance->status !== 'cancelada') {
                        $buttons .= '<button class="btn-action edit" onclick="editMaintenance(' . $maintenance->id . ')" title="Editar"><i class="fas fa-edit"></i></button>';
                    }
                    
                    // Marcar como em andamento
                    if ($maintenance->status === 'agendada') {
                        $buttons .= '<button class="btn-action start" onclick="startMaintenance(' . $maintenance->id . ')" title="Iniciar manutenção"><i class="fas fa-play"></i></button>';
                    }
                    
                    // Concluir manutenção
                    if ($maintenance->status === 'em_andamento' || $maintenance->status === 'agendada') {
                        $buttons .= '<button class="btn-action complete" onclick="completeMaintenance(' . $maintenance->id . ')" title="Concluir manutenção"><i class="fas fa-check-circle"></i></button>';
                    }
                    
                    // Cancelar (apenas se não estiver concluída)
                    if ($maintenance->status !== 'concluida' && $maintenance->status !== 'cancelada') {
                        $buttons .= '<button class="btn-action cancel" onclick="cancelMaintenance(' . $maintenance->id . ')" title="Cancelar"><i class="fas fa-times-circle"></i></button>';
                    }
                    
                    // Eliminar
                    $buttons .= '<button class="btn-action delete" onclick="confirmDeleteMaintenance(' . $maintenance->id . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                    
                    $buttons .= '</div>';
                    
                    return $buttons;
                })
                ->filterColumn('asset_info', function($query, $keyword) {
                    $query->whereHas('asset', function($q) use ($keyword) {
                        $q->where('code', 'like', "%{$keyword}%")
                          ->orWhere('name', 'like', "%{$keyword}%")
                          ->orWhere('serial_number', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('assigned_to', function($query, $keyword) {
                    $query->whereHas('asset.employee', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhereHas('company', function($q2) use ($keyword) {
                              $q2->where('name', 'like', "%{$keyword}%");
                          });
                    });
                })
                ->orderColumn('scheduled_date_formatted', 'scheduled_date $1')
                ->orderColumn('cost_formatted', 'cost $1')
                ->rawColumns([
                    'checkbox',
                    'asset_info',
                    'type_badge',
                    'status_badge',
                    'result_badge',
                    'completion_info',
                    'cost_formatted',
                    'duration_info',
                    'assigned_to',
                    'provider_info',
                    'actions'
                ])
                ->toJson();

        } catch (\Exception $e) {
            \Log::error('Erro no DataTables de manutenções: ' . $e->getMessage());
            
            return response()->json([
                'draw' => $request->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Erro ao carregar dados'
            ], 500);
        }
    }

    /**
     * Apply quick filters
     */
    private function applyQuickFilter($query, $filter)
    {
        $today = now()->format('Y-m-d');
        $sevenDaysFromNow = now()->addDays(7)->format('Y-m-d');
        $thirtyDaysFromNow = now()->addDays(30)->format('Y-m-d');

        switch($filter) {
            case 'today':
                return $query->whereDate('scheduled_date', $today);
            case 'this_week':
                return $query->whereBetween('scheduled_date', [$today, $sevenDaysFromNow]);
            case 'this_month':
                return $query->whereBetween('scheduled_date', [$today, $thirtyDaysFromNow]);
            case 'overdue':
                return $query->where('status', '!=', 'concluida')
                    ->whereDate('scheduled_date', '<', $today);
            case 'pending':
                return $query->whereIn('status', ['agendada', 'em_andamento']);
            case 'completed':
                return $query->where('status', 'concluida');
            case 'costly':
                return $query->where('cost', '>', 10000)
                    ->orderBy('cost', 'desc');
            default:
                return $query;
        }
    }

    /**
     * Show maintenance details
     */
    public function show(AssetMaintenance $maintenance)
    {
        $maintenance->load([
            'asset',
            'asset.employee.company',
            'asset.supplier',
            'asset.invoice',
            'asset.request'
        ]);

        return response()->json([
            'success' => true,
            'data' => $maintenance
        ]);
    }

    /**
     * Update maintenance status
     */
    public function updateStatus(Request $request, AssetMaintenance $maintenance)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:agendada,em_andamento,concluida,cancelada',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'status' => $request->status,
                'notes' => $request->notes
            ];

            // Se estiver concluindo, adiciona data de conclusão
            if ($request->status === 'concluida') {
                $updateData['completed_date'] = now();
            }

            // Se estiver iniciando, adiciona data de início
            if ($request->status === 'em_andamento') {
                $updateData['started_date'] = now();
            }

            $maintenance->update($updateData);

            // Atualizar status do ativo se necessário
            if ($request->status === 'concluida' || $request->status === 'cancelada') {
                $asset = $maintenance->asset;
                if ($asset) {
                    $newAssetStatus = $asset->employee_id ? 'atribuido' : 'disponivel';
                    $asset->update(['asset_status' => $newAssetStatus]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status da manutenção atualizado!',
                'data' => $maintenance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update maintenance
     */
    public function update(Request $request, AssetMaintenance $maintenance)
    {
        $validator = Validator::make($request->all(), [
            'maintenance_type' => 'required|in:preventiva,corretiva,preditiva',
            'description' => 'required|string|max:500',
            'scheduled_date' => 'required|date',
            'estimated_duration' => 'nullable|integer|min:1',
            'maintenance_provider' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $maintenance->update([
                'maintenance_type' => $request->maintenance_type,
                'description' => $request->description,
                'scheduled_date' => $request->scheduled_date,
                'estimated_duration' => $request->estimated_duration,
                'maintenance_provider' => $request->maintenance_provider,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Manutenção atualizada!',
                'data' => $maintenance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete maintenance
     */
    public function destroy(AssetMaintenance $maintenance)
    {
        try {
            // Verificar se pode ser deletada (apenas se não estiver em andamento ou concluída)
            if (in_array($maintenance->status, ['em_andamento', 'concluida'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar manutenções em andamento ou concluídas.'
                ], 400);
            }

            $maintenance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Manutenção eliminada!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get maintenance statistics
     */
    public function stats()
    {
        $stats = [
            'total' => AssetMaintenance::count(),
            'agendada' => AssetMaintenance::where('status', 'agendada')->count(),
            'em_andamento' => AssetMaintenance::where('status', 'em_andamento')->count(),
            'concluida' => AssetMaintenance::where('status', 'concluida')->count(),
            'cancelada' => AssetMaintenance::where('status', 'cancelada')->count(),
            'preventiva' => AssetMaintenance::where('maintenance_type', 'preventiva')->count(),
            'corretiva' => AssetMaintenance::where('maintenance_type', 'corretiva')->count(),
            'preditiva' => AssetMaintenance::where('maintenance_type', 'preditiva')->count(),
            'total_cost' => AssetMaintenance::whereNotNull('cost')->sum('cost'),
            'overdue' => AssetMaintenance::where('status', '!=', 'concluida')
                ->whereDate('scheduled_date', '<', now())
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export maintenance data
     */
    public function export(Request $request)
    {
        $query = AssetMaintenance::with([
            'asset',
            'asset.employee.company',
            'asset.supplier'
        ]);

        // Aplicar filtros
        if ($request->filled('quick_filter') && $request->quick_filter !== 'all') {
            $query = $this->applyQuickFilter($query, $request->quick_filter);
        }

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        $maintenances = $query->get();

        $data = $maintenances->map(function($maintenance) {
            return [
                'ID' => $maintenance->id,
                'Código Ativo' => $maintenance->asset->code ?? 'N/A',
                'Nome Ativo' => $maintenance->asset->name ?? 'N/A',
                'Tipo' => self::MAINTENANCE_TYPES[$maintenance->maintenance_type] ?? $maintenance->maintenance_type,
                'Descrição' => $maintenance->description,
                'Status' => self::MAINTENANCE_STATUSES[$maintenance->status] ?? $maintenance->status,
                'Resultado' => $maintenance->result ? (self::MAINTENANCE_RESULTS[$maintenance->result] ?? $maintenance->result) : 'N/A',
                'Data Agendada' => $maintenance->scheduled_date ? $maintenance->scheduled_date->format('d/m/Y') : 'N/A',
                'Data Conclusão' => $maintenance->completed_date ? $maintenance->completed_date->format('d/m/Y') : 'N/A',
                'Duração Estimada (dias)' => $maintenance->estimated_duration ?? 'N/A',
                'Duração Real (dias)' => $maintenance->actual_duration ?? 'N/A',
                'Custo (MT)' => $maintenance->cost ? number_format($maintenance->cost, 2, ',', '.') : 'N/A',
                'Fornecedor' => $maintenance->maintenance_provider ?? 'N/A',
                'Técnico' => $maintenance->technician_name ?? 'N/A',
                'Colaborador' => $maintenance->asset->employee->name ?? 'N/A',
                'Empresa' => $maintenance->asset->employee->company->name ?? 'N/A',
                'Observações' => $maintenance->notes ?? 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:start,complete,cancel,delete',
            'maintenance_ids' => 'required|array',
            'maintenance_ids.*' => 'exists:asset_maintenances,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $maintenances = AssetMaintenance::whereIn('id', $request->maintenance_ids)->get();
            $results = [];
            $successCount = 0;

            foreach ($maintenances as $maintenance) {
                try {
                    switch ($request->action) {
                        case 'start':
                            if ($maintenance->status === 'agendada') {
                                $maintenance->update([
                                    'status' => 'em_andamento',
                                    'started_date' => now()
                                ]);
                                $results[$maintenance->id] = 'Iniciada com sucesso';
                                $successCount++;
                            } else {
                                $results[$maintenance->id] = 'Não pode ser iniciada';
                            }
                            break;

                        case 'complete':
                            if (in_array($maintenance->status, ['agendada', 'em_andamento'])) {
                                $maintenance->update([
                                    'status' => 'concluida',
                                    'completed_date' => now()
                                ]);
                                $results[$maintenance->id] = 'Concluída com sucesso';
                                $successCount++;
                            } else {
                                $results[$maintenance->id] = 'Não pode ser concluída';
                            }
                            break;

                        case 'cancel':
                            if ($maintenance->status !== 'concluida') {
                                $maintenance->update(['status' => 'cancelada']);
                                $results[$maintenance->id] = 'Cancelada com sucesso';
                                $successCount++;
                            } else {
                                $results[$maintenance->id] = 'Não pode ser cancelada';
                            }
                            break;

                        case 'delete':
                            if (!in_array($maintenance->status, ['em_andamento', 'concluida'])) {
                                $maintenance->delete();
                                $results[$maintenance->id] = 'Eliminada';
                                $successCount++;
                            } else {
                                $results[$maintenance->id] = 'Não pode ser eliminada';
                            }
                            break;
                    }
                } catch (\Exception $e) {
                    $results[$maintenance->id] = 'Erro: ' . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => $successCount > 0,
                'message' => "Operação realizada em {$successCount} manutenção(ões)",
                'results' => $results,
                'success_count' => $successCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }
}