<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class RequestController extends Controller
{
    /**
     * Verificar se o usuário tem permissão de admin
     */
    private function checkAdminPermission()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem executar esta ação.'
            ], 403);
        }
        return null;
    }

    /**
     * Mostrar lista de requisições
     */
    public function index()
    {
        $projects = Project::all();
        $suppliers = Supplier::all();
        $invoices = Invoice::all();
        $shipments = Shipment::all();
        
        return view('requests.index', compact('projects', 'suppliers', 'invoices', 'shipments'));
    }

    /**
     * Retornar dados para DataTable (ativas)
     */
    public function data(HttpRequest $request)
    {
        $query = RequestModel::with([
            'project' => function($query) {
                $query->withTrashed()->select('id', 'name', 'deleted_at');
            },
            'supplier' => function($query) {
                $query->withTrashed()->select('id', 'name', 'deleted_at');
            },
            'invoice' => function($query) {
                $query->withTrashed()->select('id', 'number', 'deleted_at');
            },
            'shipment' => function($query) {
                $query->withTrashed()->select('id', 'guide', 'deleted_at');
            }
        ])->select([
            'id', 'code', 'date', 'type', 'description', 
            'project_id', 'supplier_id', 'invoice_id', 'shipment_id',
            'process_status', 'incomplete_reason', 'created_at', 'deleted_at'
        ]);
        
        // Check if we want trashed items
        if ($request->has('view') && $request->view === 'inactive') {
            $query = RequestModel::onlyTrashed()->with([
                'project' => function($query) {
                    $query->withTrashed()->select('id', 'name', 'deleted_at');
                },
                'supplier' => function($query) {
                    $query->withTrashed()->select('id', 'name', 'deleted_at');
                },
                'invoice' => function($query) {
                    $query->withTrashed()->select('id', 'number', 'deleted_at');
                },
                'shipment' => function($query) {
                    $query->withTrashed()->select('id', 'guide', 'deleted_at');
                }
            ])->select([
                'id', 'code', 'date', 'type', 'description', 
                'project_id', 'supplier_id', 'invoice_id', 'shipment_id',
                'process_status', 'incomplete_reason', 'created_at', 'deleted_at'
            ]);
        }
        
        // Filter by project if specified
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by type if specified
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by process status if specified
        if ($request->has('process_status') && $request->process_status) {
            $query->where('process_status', $request->process_status);
        }
        
        return DataTables::eloquent($query)
            ->addColumn('actions', function($request) {
                return '';
            })
            ->editColumn('project_id', function($request) {
                if ($request->relationLoaded('project') && $request->project) {
                    return $request->project->name;
                }
                return null;
            })
            ->editColumn('supplier_id', function($request) {
                if ($request->relationLoaded('supplier') && $request->supplier) {
                    return $request->supplier->name;
                }
                return null;
            })
            ->editColumn('invoice_id', function($request) {
                if ($request->relationLoaded('invoice') && $request->invoice) {
                    return $request->invoice->number;
                }
                return null;
            })
            ->editColumn('shipment_id', function($request) {
                if ($request->relationLoaded('shipment') && $request->shipment) {
                    return $request->shipment->guide;
                }
                return null;
            })
            ->editColumn('type', function($request) {
                return $request->type == 'internal' ? 'Interna' : 'Externa';
            })
            ->editColumn('process_status', function($request) {
                if ($request->process_status == 'completo') {
                    return 'Completo';
                } else {
                    return 'Incompleto';
                }
            })
            ->editColumn('date', function($request) {
                return $request->date ? $request->date->format('Y-m-d') : null;
            })
            ->editColumn('description', function($request) {
                return $request->description ?: null;
            })
             ->editColumn('incomplete_reason', function($request) {
                return $request->incomplete_reason ?: null;
            })
            ->editColumn('created_at', function($request) {
                return $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : null;
            })
            ->editColumn('deleted_at', function($request) {
                return $request->deleted_at ? $request->deleted_at->format('Y-m-d H:i:s') : null;
            })
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed(HttpRequest $request)
    {
        $query = RequestModel::onlyTrashed()->with([
            'project' => function($query) {
                $query->withTrashed();
            },
            'supplier' => function($query) {
                $query->withTrashed();
            },
            'invoice' => function($query) {
                $query->withTrashed();
            },
            'shipment' => function($query) {
                $query->withTrashed();
            }
        ]);
        
        return DataTables::eloquent($query)
            ->addColumn('actions', function($request) {
                return '';
            })
            ->editColumn('project_id', function($request) {
                if ($request->project) {
                    return $request->project->trashed() 
                        ? '<span class="text-danger"><i class="fas fa-trash me-1"></i>' . e($request->project->name) . '</span>'
                        : e($request->project->name);
                } else {
                    return 'Sem Projeto';
                }
            })
            ->editColumn('supplier_id', function($request) {
                if ($request->supplier) {
                    return $request->supplier->trashed() 
                        ? '<span class="text-danger"><i class="fas fa-trash me-1"></i>' . e($request->supplier->name) . '</span>'
                        : e($request->supplier->name);
                } else {
                    return 'Sem Fornecedor';
                }
            })
            ->editColumn('invoice_id', function($request) {
                if ($request->invoice) {
                    return $request->invoice->trashed() 
                        ?  e($request->invoice->number) 
                        : e($request->invoice->number);
                } else {
                    return 'Sem Fatura';
                }
            })
            ->editColumn('shipment_id', function($request) {
                if ($request->shipment) {
                    return $request->shipment->trashed() 
                        ? e($request->shipment->guide) 
                        : e($request->shipment->guide);
                } else {
                    return 'Sem Remessa';
                }
            })
            ->editColumn('type', function($request) {
                if ($request->type == 'internal') {
                    return 'Interna';
                } else {
                    return 'Externa';
                }
            })
            ->editColumn('process_status', function($request) {
                return $request->process_status == 'completo' ? 'Completo' : 'Incompleto';
            })
            ->editColumn('date', function($request) {
                return $request->date->format('Y-m-d');
            })
            ->editColumn('created_at', function($request) {
                return $request->created_at->format('Y-m-d H:i:s');
            })
            ->editColumn('deleted_at', function($request) {
                return $request->deleted_at ? $request->deleted_at->format('Y-m-d H:i:s') : null;
            })
            ->rawColumns(['actions', 'project_id', 'supplier_id', 'invoice_id', 'shipment_id'])
            ->make(true);
    }

    /**
     * Criar nova requisição
     */
    public function store(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:requests,code',
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'type' => 'required|in:internal,external',
            'description' => 'nullable|string|max:500',
            'project_id' => 'nullable|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'shipment_id' => 'nullable|exists:shipments,id',
        ], [
            'code.required' => 'O código da requisição é obrigatório.',
            'code.unique' => 'Este código já está registado.',
            'code.max' => 'O código não pode ter mais de 50 caracteres.',
            'date.required' => 'A data da requisição é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da requisição não pode ser no futuro.',
            'type.required' => 'O tipo de requisição é obrigatório.',
            'type.in' => 'O tipo deve ser Interna ou Externa.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'supplier_id.exists' => 'O fornecedor selecionado não existe.',
            'invoice_id.exists' => 'A fatura selecionada não existe.',
            'shipment_id.exists' => 'A remessa selecionada não existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $req = RequestModel::create([
                'code' => $request->code,
                'date' => $request->date,
                'type' => $request->type,
                'description' => $request->description,
                'project_id' => $request->project_id,
                'supplier_id' => $request->type === 'external' ? $request->supplier_id : null,
                'invoice_id' => $request->type === 'external' ? $request->invoice_id : null,
                'shipment_id' => $request->type === 'external' ? $request->shipment_id : null,
            ]);

            // O status do processo é atualizado automaticamente no model

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Requisição criada com sucesso!',
                'data' => $req->load(['project', 'supplier', 'invoice', 'shipment'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar requisição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar requisição
     */
    public function edit(RequestModel $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->load(['project', 'supplier', 'invoice', 'shipment'])
        ]);
    }

    /**
     * Atualizar requisição
     */
    public function update(HttpRequest $httpRequest, RequestModel $request)
    {
        $validator = Validator::make($httpRequest->all(), [
            'code' => 'required|string|max:50|unique:requests,code,' . $request->id,
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'type' => 'required|in:internal,external',
            'description' => 'nullable|string|max:500',
            'project_id' => 'nullable|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'shipment_id' => 'nullable|exists:shipments,id',
        ], [
            'code.required' => 'O código da requisição é obrigatório.',
            'code.unique' => 'Este código já está registado.',
            'code.max' => 'O código não pode ter mais de 50 caracteres.',
            'date.required' => 'A data da requisição é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da requisição não pode ser no futuro.',
            'type.required' => 'O tipo de requisição é obrigatório.',
            'type.in' => 'O tipo deve ser Interna ou Externa.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'supplier_id.exists' => 'O fornecedor selecionado não existe.',
            'invoice_id.exists' => 'A fatura selecionada não existe.',
            'shipment_id.exists' => 'A remessa selecionada não existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = [
                'code' => $httpRequest->code,
                'date' => $httpRequest->date,
                'type' => $httpRequest->type,
                'description' => $httpRequest->description,
                'project_id' => $httpRequest->project_id,
            ];

            // Para requisições externas, permitir supplier/invoice/shipment
            if ($httpRequest->type === 'external') {
                $data['supplier_id'] = $httpRequest->supplier_id;
                $data['invoice_id'] = $httpRequest->invoice_id;
                $data['shipment_id'] = $httpRequest->shipment_id;
            } else {
                // Para requisições internas, limpar esses campos
                $data['supplier_id'] = null;
                $data['invoice_id'] = null;
                $data['shipment_id'] = null;
            }

            $request->update($data);

            // O status do processo é atualizado automaticamente no model

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Requisição atualizada com sucesso!',
                'data' => $request->fresh(['project', 'supplier', 'invoice', 'shipment'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar requisição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar requisição (soft delete)
     */
    public function destroy(RequestModel $request)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar requisições.'
            ], 403);
        }

        try {
            // Verificar se tem ativos associados
            if ($request->assets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar esta requisição porque possui ativos associados.'
                ], 422);
            }

            $request->delete();

            return response()->json([
                'success' => true,
                'message' => 'Requisição eliminada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar requisição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar requisição eliminada
     */
    public function restore($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem restaurar requisições.'
            ], 403);
        }

        try {
            $request = RequestModel::withTrashed()->findOrFail($id);
            
            // Verificar se o projeto ainda existe (não foi force deleted)
            if ($request->project_id && !Project::withTrashed()->find($request->project_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível restaurar porque o projeto associado não existe mais.'
                ], 422);
            }
            
            $request->restore();

            return response()->json([
                'success' => true,
                'message' => 'Requisição restaurada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar requisição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente requisição
     */
    public function forceDelete($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar permanentemente requisições.'
            ], 403);
        }

        try {
            $request = RequestModel::withTrashed()->findOrFail($id);
            
            // Verificar se tem ativos associados
            if ($request->assets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar permanentemente porque existem ativos associados.'
                ], 422);
            }
            
            $request->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Requisição eliminada permanentemente!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar requisição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar requisições para select2
     */
    public function search(HttpRequest $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        
        $query = RequestModel::select('id', 'code as text', 'type', 'process_status')
            ->where('code', 'like', "%{$search}%");
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $requests = $query->limit(10)->get();
            
        return response()->json([
            'results' => $requests
        ]);
    }

    /**
     * Buscar requisições incompletas
     */
    public function searchIncomplete(HttpRequest $request)
    {
        $search = $request->get('search');
        
        $requests = RequestModel::select('id', 'code as text', 'incomplete_reason')
            ->where('process_status', 'incompleto')
            ->where('code', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $requests
        ]);
    }

    /**
     * Relatório de requisições por período
     */
    public function report(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|in:internal,external',
            'project_id' => 'nullable|exists:projects,id',
            'process_status' => 'nullable|in:completo,incompleto',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = RequestModel::with(['project', 'supplier', 'invoice', 'shipment', 'assets'])
                ->whereBetween('date', [$request->start_date, $request->end_date]);
            
            if ($request->type) {
                $query->where('type', $request->type);
            }
            
            if ($request->project_id) {
                $query->where('project_id', $request->project_id);
            }
            
            if ($request->process_status) {
                $query->where('process_status', $request->process_status);
            }
            
            $requests = $query->orderBy('date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
                'count' => $requests->count(),
                'period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ],
                'stats' => [
                    'internal' => $requests->where('type', 'internal')->count(),
                    'external' => $requests->where('type', 'external')->count(),
                    'complete' => $requests->where('process_status', 'completo')->count(),
                    'incomplete' => $requests->where('process_status', 'incompleto')->count(),
                    'with_project' => $requests->whereNotNull('project_id')->count(),
                    'without_project' => $requests->whereNull('project_id')->count(),
                    'with_supplier' => $requests->whereNotNull('supplier_id')->count(),
                    'with_invoice' => $requests->whereNotNull('invoice_id')->count(),
                    'with_shipment' => $requests->whereNotNull('shipment_id')->count(),
                    'total_assets' => $requests->sum(function($req) {
                        return $req->assets->count();
                    }),
                    'total_assets_value' => $requests->sum(function($req) {
                        return $req->assets->sum('total_value');
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas de requisições
     */
    public function statistics()
    {
        try {
            $total = RequestModel::count();
            $totalWithTrashed = RequestModel::withTrashed()->count();
            $trashed = RequestModel::onlyTrashed()->count();
            
            $today = RequestModel::whereDate('date', now()->toDateString())->count();
            $thisWeek = RequestModel::whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();
            $thisMonth = RequestModel::whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count();
            
            $internal = RequestModel::internal()->count();
            $external = RequestModel::external()->count();
            
            $complete = RequestModel::where('process_status', 'completo')->count();
            $incomplete = RequestModel::where('process_status', 'incompleto')->count();
            
            $withProject = RequestModel::whereNotNull('project_id')->count();
            $withoutProject = RequestModel::whereNull('project_id')->count();
            
            $withSupplier = RequestModel::external()->whereNotNull('supplier_id')->count();
            $withoutSupplier = RequestModel::external()->whereNull('supplier_id')->count();
            
            $withInvoice = RequestModel::external()->whereNotNull('invoice_id')->count();
            $withoutInvoice = RequestModel::external()->whereNull('invoice_id')->count();
            
            $withShipment = RequestModel::external()->whereNotNull('shipment_id')->count();
            $withoutShipment = RequestModel::external()->whereNull('shipment_id')->count();

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total' => $total,
                    'total_with_trashed' => $totalWithTrashed,
                    'trashed' => $trashed,
                    'today' => $today,
                    'this_week' => $thisWeek,
                    'this_month' => $thisMonth,
                    'internal' => $internal,
                    'external' => $external,
                    'complete' => $complete,
                    'incomplete' => $incomplete,
                    'with_project' => $withProject,
                    'without_project' => $withoutProject,
                    'with_supplier' => $withSupplier,
                    'without_supplier' => $withoutSupplier,
                    'with_invoice' => $withInvoice,
                    'without_invoice' => $withoutInvoice,
                    'with_shipment' => $withShipment,
                    'without_shipment' => $withoutShipment,
                    'internal_percentage' => $total > 0 ? round(($internal / $total) * 100, 1) : 0,
                    'external_percentage' => $total > 0 ? round(($external / $total) * 100, 1) : 0,
                    'complete_percentage' => $total > 0 ? round(($complete / $total) * 100, 1) : 0,
                    'last_month' => RequestModel::whereMonth('date', now()->subMonth()->month)
                        ->whereYear('date', now()->subMonth()->year)
                        ->count(),
                    'recent' => RequestModel::where('date', '>=', now()->subDays(7))->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar código automático
     */
    public function generateCode()
    {
        try {
            $code = RequestModel::generateCode();
            
            return response()->json([
                'success' => true,
                'code' => $code
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar código: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter requisições por projeto
     */
    public function byProject($projectId)
    {
        try {
            $project = Project::withTrashed()->findOrFail($projectId);
            $requests = $project->requests()
                ->with(['supplier', 'invoice', 'shipment', 'assets'])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'project' => $project,
                'requests' => $requests,
                'count' => $requests->count(),
                'statistics' => [
                    'total' => $requests->count(),
                    'internal' => $requests->where('type', 'internal')->count(),
                    'external' => $requests->where('type', 'external')->count(),
                    'complete' => $requests->where('process_status', 'completo')->count(),
                    'total_assets' => $requests->sum(function($req) {
                        return $req->assets->count();
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter requisições do projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter opções para formulário
     */
    public function getFormOptions()
    {
        try {
            return response()->json([
                'success' => true,
                'projects' => Project::select('id', 'name', 'code')->get(),
                'suppliers' => Supplier::select('id', 'name', 'nuit')->get(),
                'invoices' => Invoice::with('documents')
                    ->select('id', 'number', 'date', 'status')
                    ->orderBy('date', 'desc')
                    ->limit(100)
                    ->get(),
                'shipments' => Shipment::with('documents')
                    ->select('id', 'guide', 'date', 'status')
                    ->orderBy('date', 'desc')
                    ->limit(100)
                    ->get(),
                'types' => [
                    ['value' => 'internal', 'label' => 'Interna'],
                    ['value' => 'external', 'label' => 'Externa']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter opções: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar status manualmente
     */
    public function updateStatus(RequestModel $request)
    {
        try {
            DB::beginTransaction();
            
            $request->updateProcessStatus();
            $request->save();
            
            // Atualizar status dos ativos relacionados
            foreach ($request->assets as $asset) {
                $asset->updateProcessStatus();
                $asset->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'data' => [
                    'process_status' => $request->process_status,
                    'process_status_label' => $request->process_status_label,
                    'incomplete_reason' => $request->incomplete_reason
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Alterar estado da requisição
 */
public function changeStatus(HttpRequest $httpRequest, RequestModel $request)
{
    // Verificar permissão de admin
    if (!auth()->user()->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Apenas administradores podem alterar o estado das requisições.'
        ], 403);
    }

    $validator = Validator::make($httpRequest->all(), [
        'process_status' => 'required|in:completo,incompleto',
        'incomplete_reason' => 'required_if:process_status,incompleto|nullable|string|max:500',
    ], [
        'process_status.required' => 'O estado é obrigatório.',
        'process_status.in' => 'Estado inválido.',
        'incomplete_reason.required_if' => 'A razão da incompletude é obrigatória quando o estado é incompleto.',
        'incomplete_reason.max' => 'A razão não pode ter mais de 500 caracteres.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $request->process_status = $httpRequest->process_status;
        $request->incomplete_reason = $httpRequest->process_status === 'incompleto' 
            ? $httpRequest->incomplete_reason 
            : null;
        $request->save();

        // Se ficou completo, verificar se todos os relacionamentos estão ok
        if ($request->process_status === 'completo') {
            // Atualizar status dos ativos relacionados
            foreach ($request->assets as $asset) {
                $asset->updateProcessStatus();
                $asset->save();
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Estado da requisição atualizado com sucesso!',
            'data' => [
                'process_status' => $request->process_status,
                'process_status_label' => $request->process_status_label,
                'incomplete_reason' => $request->incomplete_reason
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erro ao alterar estado: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Verificar status dos relacionamentos
 */
public function checkRelationshipsStatus(RequestModel $request)
{
    try {
        $issues = [];

        // Verificar projeto
        if (!$request->project) {
            $issues[] = 'Projeto não associado';
        }

        // Verificar fornecedor
        if (!$request->supplier) {
            $issues[] = 'Fornecedor não associado';
        }

        // Verificar fatura
        if (!$request->invoice) {
            $issues[] = 'Fatura não associada';
        } elseif ($request->invoice->status === 'incompleto') {
            $issues[] = 'Fatura incompleta: ' . $request->invoice->incomplete_reason;
        }

        // Verificar remessa
        if (!$request->shipment) {
            $issues[] = 'Remessa não associada';
        } elseif ($request->shipment->status === 'incompleto') {
            $issues[] = 'Remessa incompleta: ' . $request->shipment->incomplete_reason;
        }

        return response()->json([
            'success' => true,
            'has_issues' => count($issues) > 0,
            'issues' => $issues,
            'process_status' => $request->process_status,
            'incomplete_reason' => $request->incomplete_reason
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao verificar status: ' . $e->getMessage()
        ], 500);
    }
}
}