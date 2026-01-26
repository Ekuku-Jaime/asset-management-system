<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    /**
     * Mostrar lista de fornecedores
     */
    public function index()
    {
        return view('suppliers.index');
    }

    /**
     * Retornar dados para DataTable
     */
    public function data()
    {
        $suppliers = Supplier::select(['id', 'name', 'nuit', 'created_at', 'deleted_at']);
        
        return DataTables::of($suppliers)
            ->addIndexColumn()
            ->addColumn('actions', function($supplier) {
                return '';
            })
            ->editColumn('created_at', function($supplier) {
                return $supplier->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($supplier) {
                return $supplier->deleted_at ? 
                    '<span class="badge bg-danger">Eliminado</span>' : 
                    '<span class="badge bg-success">Ativo</span>';
            })
            ->rawColumns(['actions', 'deleted_at'])
            ->make(true);
    }

    /**
     * Criar novo fornecedor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nuit' => 'required|string|max:20|unique:suppliers,nuit',
        ], [
            'name.required' => 'O nome do fornecedor é obrigatório.',
            'nuit.required' => 'O NUIT é obrigatório.',
            'nuit.unique' => 'Este NUIT já está registado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier = Supplier::create([
                'name' => $request->name,
                'nuit' => $request->nuit,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor criado com sucesso!',
                'data' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar fornecedor
     */
    public function edit(Supplier $supplier)
    {
        return response()->json([
            'success' => true,
            'data' => $supplier
        ]);
    }

    /**
     * Atualizar fornecedor
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nuit' => 'required|string|max:20|unique:suppliers,nuit,' . $supplier->id,
        ], [
            'name.required' => 'O nome do fornecedor é obrigatório.',
            'nuit.required' => 'O NUIT é obrigatório.',
            'nuit.unique' => 'Este NUIT já está registado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier->update([
                'name' => $request->name,
                'nuit' => $request->nuit,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor atualizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar fornecedor (soft delete)
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor eliminado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar fornecedor eliminado
     */
    public function restore($id)
    {
        try {
            $supplier = Supplier::withTrashed()->findOrFail($id);
            $supplier->restore();

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor restaurado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar fornecedores para select2
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $suppliers = Supplier::select('id', 'name as text')
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $suppliers
        ]);
    }

    /**
 * Retornar dados eliminados para DataTable
 */
public function dataTrashed()
{
    $suppliers = Supplier::onlyTrashed()->select(['id', 'name', 'nuit', 'created_at', 'deleted_at']);
    
    return DataTables::of($suppliers)
        ->addIndexColumn()
        ->addColumn('actions', function($supplier) {
            return '';
        })
        ->editColumn('created_at', function($supplier) {
            return $supplier->created_at->format('d/m/Y H:i');
        })
        ->editColumn('deleted_at', function($supplier) {
            return $supplier->deleted_at ? 
                $supplier->deleted_at->format('d/m/Y H:i') : 
                '';
        })
        ->rawColumns(['actions'])
        ->make(true);
}

/**
 * Eliminar permanentemente fornecedor
 */
public function forceDelete($id)
{
    try {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Fornecedor eliminado permanentemente!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao eliminar fornecedor: ' . $e->getMessage()
        ], 500);
    }
}
}