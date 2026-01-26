<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ShipmentController extends Controller
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
     * Mostrar lista de remessas
     */
    public function index()
    {
        return view('shipments.index');
    }

    /**
     * Retornar dados para DataTable
     */
 /**
 * Retornar dados para DataTable
 */
public function data(Request $request)
{
    $query = Shipment::query();
    
    // Check if we want trashed items
    if ($request->has('view') && $request->view === 'inactive') {
        $query = Shipment::onlyTrashed();
    }
    
    return DataTables::eloquent($query)
        ->addColumn('actions', function($shipment) {
            return '';
        })
        ->editColumn('guide', function($shipment) {
            return strtoupper($shipment->guide);
        })
        ->editColumn('date', function($shipment) {
            return Carbon::parse($shipment->date);
        })
        ->editColumn('created_at', function($shipment) {
            return Carbon::parse($shipment->created_at);
        })
        ->editColumn('deleted_at', function($shipment) {
            return $shipment->deleted_at ? Carbon::parse($shipment->deleted_at) : null;
        })
        ->rawColumns(['actions'])
        ->make(true);
}
    /**
     * Retornar dados eliminados para DataTable
     */
   public function dataTrashed(Request $request)
{
    return DataTables::eloquent(Shipment::onlyTrashed())
        ->addColumn('actions', '')
        ->editColumn('guide', function($shipment) {
            return strtoupper($shipment->guide);
        })
        ->rawColumns(['actions'])
        ->toJson();
}
    /**
     * Criar nova remessa
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guide' => 'required|string|max:100|unique:shipments,guide',
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
        ], [
            'guide.required' => 'O guia da remessa é obrigatório.',
            'guide.unique' => 'Este guia já está registado.',
            'guide.max' => 'O guia não pode ter mais de 100 caracteres.',
            'date.required' => 'A data da remessa é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da remessa não pode ser no futuro.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shipment = Shipment::create([
                'guide' => $request->guide,
                'date' => $request->date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Remessa criada com sucesso!',
                'data' => $shipment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar remessa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar remessa
     */
    public function edit(Shipment $shipment)
    {
        return response()->json([
            'success' => true,
            'data' => $shipment
        ]);
    }

    /**
     * Atualizar remessa
     */
    public function update(Request $request, Shipment $shipment)
    {
        $validator = Validator::make($request->all(), [
            'guide' => 'required|string|max:100|unique:shipments,guide,' . $shipment->id,
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
        ], [
            'guide.required' => 'O guia da remessa é obrigatório.',
            'guide.unique' => 'Este guia já está registado.',
            'guide.max' => 'O guia não pode ter mais de 100 caracteres.',
            'date.required' => 'A data da remessa é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da remessa não pode ser no futuro.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shipment->update([
                'guide' => $request->guide,
                'date' => $request->date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Remessa atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar remessa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar remessa (soft delete)
     */
    public function destroy(Shipment $shipment)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar remessas.'
            ], 403);
        }

        try {
            $shipment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Remessa eliminada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar remessa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar remessa eliminada
     */
    public function restore($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem restaurar remessas.'
            ], 403);
        }

        try {
            $shipment = Shipment::withTrashed()->findOrFail($id);
            $shipment->restore();

            return response()->json([
                'success' => true,
                'message' => 'Remessa restaurada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar remessa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente remessa
     */
    public function forceDelete($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar permanentemente remessas.'
            ], 403);
        }

        try {
            $shipment = Shipment::withTrashed()->findOrFail($id);
            $shipment->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Remessa eliminada permanentemente!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar remessa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar remessas para select2
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $shipments = Shipment::select('id', 'guide as text')
            ->where('guide', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $shipments
        ]);
    }

    /**
     * Relatório de remessas por período
     */
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shipments = Shipment::whereBetween('date', [$request->start_date, $request->end_date])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $shipments,
                'count' => $shipments->count(),
                'period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
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
     * Estatísticas de remessas
     */
    public function statistics()
    {
        try {
            $total = Shipment::count();
            $today = Shipment::whereDate('date', now()->toDateString())->count();
            $thisWeek = Shipment::whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();
            $thisMonth = Shipment::whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count();

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total' => $total,
                    'today' => $today,
                    'this_week' => $thisWeek,
                    'this_month' => $thisMonth,
                    'last_month' => Shipment::whereMonth('date', now()->subMonth()->month)
                        ->whereYear('date', now()->subMonth()->year)
                        ->count(),
                    'recent' => Shipment::where('date', '>=', now()->subDays(7))->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}