<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class InvoiceController extends Controller
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
     * Mostrar lista de faturas
     */
    public function index()
    {
        return view('invoices.index');
    }

    /**
     * Retornar dados para DataTable
     */
    public function data()
    {
        $invoices = Invoice::select(['id', 'number', 'date', 'created_at', 'deleted_at']);
        
        return DataTables::of($invoices)
            ->addIndexColumn()
            ->addColumn('actions', function($invoice) {
                return '';
            })
            ->editColumn('date', function($invoice) {
                return $invoice->date;
            })
            ->editColumn('created_at', function($invoice) {
                return $invoice->created_at;
            })
            ->editColumn('deleted_at', function($invoice) {
                return $invoice->deleted_at ? 
                    '<span class="badge bg-danger">Eliminada</span>' : 
                    '<span class="badge bg-success">Ativa</span>';
            })
            ->rawColumns(['actions', 'deleted_at'])
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed()
    {
        $invoices = Invoice::onlyTrashed()
            ->select(['id', 'number', 'date', 'created_at', 'deleted_at']);
        
        return DataTables::of($invoices)
            ->addIndexColumn()
            ->addColumn('actions', function($invoice) {
                return '';
            })
            ->editColumn('date', function($invoice) {
                
                return $invoice->date;
            })
            ->editColumn('created_at', function($invoice) {
                return $invoice->created_at;
            })
            ->editColumn('deleted_at', function($invoice) {
                return $invoice->deleted_at ? 
                   $invoice->deleted_at : 
                    '';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Criar nova fatura
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string|max:50|unique:invoices,number',
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
        ], [
            'number.required' => 'O número da fatura é obrigatório.',
            'number.unique' => 'Este número de fatura já está registado.',
            'date.required' => 'A data da fatura é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da fatura não pode ser no futuro.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = Invoice::create([
                'number' => $request->number,
                'date' => $request->date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fatura criada com sucesso!',
                'data' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar fatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar fatura
     */
    public function edit(Invoice $invoice)
    {
        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Atualizar fatura
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string|max:50|unique:invoices,number,' . $invoice->id,
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
        ], [
            'number.required' => 'O número da fatura é obrigatório.',
            'number.unique' => 'Este número de fatura já está registado.',
            'date.required' => 'A data da fatura é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da fatura não pode ser no futuro.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice->update([
                'number' => $request->number,
                'date' => $request->date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fatura atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar fatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar fatura (soft delete)
     */
    public function destroy(Invoice $invoice)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar faturas.'
            ], 403);
        }

        try {
            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fatura eliminada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar fatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar fatura eliminada
     */
    public function restore($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem restaurar faturas.'
            ], 403);
        }

        try {
            $invoice = Invoice::withTrashed()->findOrFail($id);
            $invoice->restore();

            return response()->json([
                'success' => true,
                'message' => 'Fatura restaurada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar fatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente fatura
     */
    public function forceDelete($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar permanentemente faturas.'
            ], 403);
        }

        try {
            $invoice = Invoice::withTrashed()->findOrFail($id);
            $invoice->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Fatura eliminada permanentemente!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar fatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar faturas para select2
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $invoices = Invoice::select('id', 'number as text')
            ->where('number', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $invoices
        ]);
    }

    /**
     * Relatório de faturas por período
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
            $invoices = Invoice::whereBetween('date', [$request->start_date, $request->end_date])
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'count' => $invoices->count(),
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
}