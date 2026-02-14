<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProjectController extends Controller
{
    /**
     * Mostrar lista de projetos
     */
    public function index()
    {
        return view('projects.index');
    }

    /**
     * Retornar dados para DataTable (ativos)
     */
    public function data(Request $request)
    {
        $projects = Project::select([
            'id', 
            'code', 
            'name', 
            'description',
            'start_date', 
            'end_date', 
            'status', 
            'total_value',
            'created_at',
            'deleted_at'
        ]);

        // Filtro por status
        if ($request->has('status') && $request->status != '') {
            $projects->where('status', $request->status);
        }

        // Filtro por período
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $projects->whereBetween('created_at', [$dates[0], $dates[1]]);
            }
        }

        return DataTables::of($projects)
            ->addIndexColumn()
            ->editColumn('code', function($project) {
                return '<span class="badge bg-primary">' . e($project->code) . '</span>';
            })
            ->editColumn('name', function($project) {
                $initial = strtoupper(substr($project->name, 0, 1));
                $colorClass = 'project-color-' . (($project->id % 5) + 1);
                
                return '<div class="d-flex align-items-center">
                            <div class="project-avatar ' . $colorClass . ' me-3">
                                ' . $initial . '
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">' . e($project->name) . '</h6>
                                <small class="text-muted">' . e($project->code) . '</small>
                            </div>
                        </div>';
            })
            ->editColumn('description', function($project) {
                return $project->description 
                    ? '<span title="' . e($project->description) . '">' . e(Str::limit($project->description, 30)) . '</span>'
                    : '<span class="text-muted">—</span>';
            })
            ->editColumn('start_date', function($project) {
                return $project->start_date 
                    ? $project->start_date->format('d/m/Y')
                    : '<span class="text-muted">—</span>';
            })
            ->editColumn('end_date', function($project) {
                if (!$project->end_date) {
                    return '<span class="text-muted">—</span>';
                }
                
                if ($project->is_overdue && $project->status !== 'concluido') {
                    return '<span class="badge bg-danger" title="Projeto atrasado">
                                ' . $project->end_date->format('d/m/Y') . '
                                <i class="fas fa-exclamation-circle ms-1"></i>
                            </span>';
                }
                
                return $project->end_date->format('d/m/Y');
            })
            ->editColumn('status', function($project) {
                $labels = [
                    'ativo' => ['badge' => 'success', 'icon' => 'fa-check-circle'],
                    'concluido' => ['badge' => 'primary', 'icon' => 'fa-flag-checkered'],
                    'suspenso' => ['badge' => 'warning', 'icon' => 'fa-pause-circle'],
                    'cancelado' => ['badge' => 'danger', 'icon' => 'fa-times-circle']
                ];
                
                $status = $labels[$project->status] ?? ['badge' => 'secondary', 'icon' => 'fa-question-circle'];
                
                return '<span class="badge bg-' . $status['badge'] . '">
                            <i class="fas ' . $status['icon'] . ' me-1"></i>
                            ' . ucfirst($project->status) . '
                        </span>';
            })
            ->editColumn('total_value', function($project) {
                return $project->total_value > 0 
                    ? number_format($project->total_value, 2, ',', '.') . ' MT'
                    : '<span class="text-muted">—</span>';
            })
            ->editColumn('created_at', function($project) {
                return $project->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($project) {
                if ($project->trashed()) {
                    return '<span class="badge bg-danger">
                                <i class="fas fa-trash me-1"></i>
                                Eliminado em ' . $project->deleted_at->format('d/m/Y H:i') . '
                            </span>';
                }
                return '<span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Ativo
                        </span>';
            })
            ->addColumn('progress', function($project) {
                $progress = $project->progress_percentage;
                $budgetStatus = $project->budget_status;
                
                return '<div class="d-flex align-items-center gap-2">
                            <div class="flex-grow-1">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-' . $budgetStatus['class'] . '" 
                                         role="progressbar" 
                                         style="width: ' . $progress . '%"
                                         aria-valuenow="' . $progress . '" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    ' . $progress . '% concluído
                                </small>
                            </div>
                        </div>';
            })
            ->addColumn('budget', function($project) {
                $spent = $project->total_assets_value;
                $budget = $project->total_value;
                
                if ($budget <= 0) {
                    return '<span class="text-muted">Sem orçamento</span>';
                }
                
                $percentage = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0;
                $remaining = $budget - $spent;
                
                return '<div>
                            <div class="fw-bold">' . number_format($spent, 2, ',', '.') . ' MT</div>
                            <small class="text-muted">de ' . number_format($budget, 2, ',', '.') . ' MT</small>
                            <div>
                                <small class="text-' . ($remaining < 0 ? 'danger' : 'success') . '">
                                    <i class="fas fa-arrow-' . ($remaining < 0 ? 'down' : 'up') . ' me-1"></i>
                                    ' . number_format(abs($remaining), 2, ',', '.') . ' MT
                                </small>
                            </div>
                        </div>';
            })
            ->addColumn('stats', function($project) {
                return '<div class="d-flex gap-3">
                            <div class="text-center">
                                <span class="fw-bold d-block">' . $project->assets_count . '</span>
                                <small class="text-muted">Ativos</small>
                            </div>
                            <div class="text-center">
                                <span class="fw-bold d-block">' . $project->requests_count . '</span>
                                <small class="text-muted">Requisições</small>
                            </div>
                        </div>';
            })
            ->addColumn('actions', function($project) {
                if ($project->trashed()) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-success btn-restore"
                                        data-id="' . $project->id . '"
                                        data-name="' . e($project->name) . '"
                                        title="Restaurar Projeto">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-force-delete"
                                        data-id="' . $project->id . '"
                                        data-name="' . e($project->name) . '"
                                        title="Eliminar Permanentemente">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>';
                }
                
                return '<div class="btn-group btn-group-sm" role="group">
                            <a href="' . route('projects.show', $project->id) . '" 
                               class="btn btn-outline-info"
                               title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-outline-primary btn-edit"
                                    data-id="' . $project->id . '"
                                    title="Editar Projeto">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-delete"
                                    data-id="' . $project->id . '"
                                    data-name="' . e($project->name) . '"
                                    title="Eliminar Projeto">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>';
            })
            ->rawColumns([
                'code',
                'name', 
                'description',
                'start_date',
                'end_date',
                'status',
                'total_value',
                'deleted_at',
                'progress',
                'budget',
                'stats',
                'actions'
            ])
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed(Request $request)
    {
        $projects = Project::onlyTrashed()->select([
            'id', 
            'code', 
            'name', 
            'description',
            'start_date', 
            'end_date', 
            'status', 
            'total_value',
            'created_at',
            'deleted_at'
        ]);

        return DataTables::of($projects)
            ->addIndexColumn()
            ->editColumn('code', function($project) {
                return '<span class="badge bg-secondary">' . e($project->code) . '</span>';
            })
            ->editColumn('name', function($project) {
                $initial = strtoupper(substr($project->name, 0, 1));
                $colorClass = 'project-color-' . (($project->id % 5) + 1);
                
                return '<div class="d-flex align-items-center">
                            <div class="project-avatar ' . $colorClass . ' opacity-50 me-3">
                                ' . $initial . '
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-muted">' . e($project->name) . '</h6>
                                <small class="text-muted">' . e($project->code) . '</small>
                            </div>
                        </div>';
            })
            ->editColumn('status', function($project) {
                return '<span class="badge bg-secondary">
                            <i class="fas fa-trash me-1"></i>
                            Eliminado
                        </span>';
            })
            ->editColumn('deleted_at', function($project) {
                return $project->deleted_at->format('d/m/Y H:i');
            })
            ->addColumn('actions', function($project) {
                return '<div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-success btn-restore"
                                    data-id="' . $project->id . '"
                                    data-name="' . e($project->name) . '"
                                    title="Restaurar Projeto">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-force-delete"
                                    data-id="' . $project->id . '"
                                    data-name="' . e($project->name) . '"
                                    title="Eliminar Permanentemente">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['code', 'name', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Mostrar detalhes do projeto
     */
    public function show(Project $project)
    {
        $project->load(['requests.assets', 'requests.assets.employee', 'requests.assets.supplier']);
        
        return view('projects.show', compact('project'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Criar novo projeto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects,name',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:ativo,concluido,suspenso,cancelado',
            'total_value' => 'nullable|numeric|min:0',
        ], [
            'name.required' => 'O nome do projeto é obrigatório.',
            'name.unique' => 'Este nome de projeto já está registado.',
            'end_date.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'total_value.min' => 'O valor total deve ser maior ou igual a zero.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status ?? 'ativo',
                'total_value' => $request->total_value ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Projeto criado com sucesso!',
                'data' => $project
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit(Project $project)
    {
        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }

    /**
     * Atualizar projeto
     */
    public function update(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects,name,' . $project->id,
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:ativo,concluido,suspenso,cancelado',
            'total_value' => 'nullable|numeric|min:0',
        ], [
            'name.required' => 'O nome do projeto é obrigatório.',
            'name.unique' => 'Este nome de projeto já está registado.',
            'end_date.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'total_value.min' => 'O valor total deve ser maior ou igual a zero.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $project->update([
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'total_value' => $request->total_value ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Projeto atualizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar projeto (soft delete)
     */
    public function destroy(Project $project)
    {
        try {
            // Verificar se tem ativos associados
            if ($project->assets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar este projeto pois possui ativos associados.'
                ], 422);
            }

            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Projeto eliminado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar projeto eliminado
     */
    public function restore($id)
    {
        try {
            $project = Project::withTrashed()->findOrFail($id);
            $project->restore();

            return response()->json([
                'success' => true,
                'message' => 'Projeto restaurado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente projeto
     */
    public function forceDelete($id)
    {
        try {
            $project = Project::withTrashed()->findOrFail($id);
            
            // Verificar novamente se tem ativos associados
            if ($project->assets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar permanentemente este projeto pois possui ativos associados.'
                ], 422);
            }
            
            $project->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Projeto eliminado permanentemente!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar projetos para select2
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        
        $projects = Project::select('id', 'code', 'name as text')
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            });
            
        if ($status) {
            $projects->where('status', $status);
        }
        
        $projects = $projects->limit(10)
            ->get()
            ->map(function($project) {
                return [
                    'id' => $project->id,
                    'text' => $project->code . ' - ' . $project->text
                ];
            });
            
        return response()->json([
            'results' => $projects
        ]);
    }

    /**
     * Estatísticas de projetos para dashboard
     */
    public function getStats()
    {
        $stats = [
            'total' => Project::count(),
            'active' => Project::active()->count(),
            'completed' => Project::completed()->count(),
            'suspended' => Project::suspended()->count(),
            'cancelled' => Project::cancelled()->count(),
            'overdue' => Project::overdue()->count(),
            'total_budget' => Project::sum('total_value'),
            'total_spent' => Project::withSum('assets', 'total_value')->get()->sum('assets_sum_total_value'),
            'with_assets' => Project::has('assets')->count(),
            'without_assets' => Project::doesntHave('assets')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}