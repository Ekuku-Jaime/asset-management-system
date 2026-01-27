<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

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
        return view('requests.index');
    }

    /**
     * Retornar dados para DataTable (ativas)
     */
    public function data(HttpRequest $request)
    {
        $query = RequestModel::query();
        
        // Check if we want trashed items
        if ($request->has('view') && $request->view === 'inactive') {
            $query = RequestModel::onlyTrashed();
        }
        
        return DataTables::eloquent($query)
            ->addColumn('actions', function($request) {
                return '';
            })
            ->editColumn('type', function($request) {
                if ($request->type == 'internal') {
                    return '<span class="badge bg-primary">Interna</span>';
                } else {
                    return '<span class="badge bg-warning text-dark">Externa</span>';
                }
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
            ->rawColumns(['actions', 'type'])
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed(HttpRequest $request)
    {
        $query = RequestModel::onlyTrashed();
        
        return DataTables::eloquent($query)
            ->addColumn('actions', function($request) {
                return '';
            })
            ->editColumn('type', function($request) {
                if ($request->type == 'internal') {
                    return 'Interna';
                } else {
                    return 'Externa';
                }
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
            ->rawColumns(['actions'])
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $req = RequestModel::create([
                'code' => $request->code,
                'date' => $request->date,
                'type' => $request->type,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Requisição criada com sucesso!',
                'data' => $req
            ]);

        } catch (\Exception $e) {
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
            'data' => $request
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $request->update([
                'code' => $httpRequest->code,
                'date' => $httpRequest->date,
                'type' => $httpRequest->type,
                'description' => $httpRequest->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Requisição atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
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
        
        $requests = RequestModel::select('id', 'code as text')
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = RequestModel::whereBetween('date', [$request->start_date, $request->end_date]);
            
            if ($request->type) {
                $query->where('type', $request->type);
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

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total' => $total,
                    'today' => $today,
                    'this_week' => $thisWeek,
                    'this_month' => $thisMonth,
                    'internal' => $internal,
                    'external' => $external,
                    'internal_percentage' => $total > 0 ? round(($internal / $total) * 100, 1) : 0,
                    'external_percentage' => $total > 0 ? round(($external / $total) * 100, 1) : 0,
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
}