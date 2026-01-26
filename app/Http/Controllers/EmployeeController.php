<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
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
     * Mostrar lista de colaboradores
     */
    public function index()
    {
        $companies = Company::orderBy('name')->get();
        return view('employees.index', compact('companies'));
    }

    /**
     * Retornar dados para DataTable
     */
    public function data()
    {
        $employees = Employee::with('company')
            ->select(['id', 'name', 'document', 'company_id', 'created_at', 'deleted_at']);
        
        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('actions', function($employee) {
                return '';
            })
            ->addColumn('company_name', function($employee) {
                return $employee->company->name ?? 'N/A';
            })
            ->editColumn('created_at', function($employee) {
                return $employee->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($employee) {
                return $employee->deleted_at ? 
                    '<span class="badge bg-danger">Eliminado</span>' : 
                    '<span class="badge bg-success">Ativo</span>';
            })
            ->rawColumns(['actions', 'deleted_at'])
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed()
    {
        $employees = Employee::onlyTrashed()
            ->with(['company' => function($query) {
                $query->withTrashed();
            }])
            ->select(['id', 'name', 'document', 'company_id', 'created_at', 'deleted_at']);
        
        return DataTables::of($employees)
            ->addIndexColumn()
            ->addColumn('actions', function($employee) {
                return '';
            })
            ->addColumn('company_name', function($employee) {
                return $employee->company->name ?? 'Empresa Eliminada';
            })
            ->editColumn('created_at', function($employee) {
                return $employee->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($employee) {
                return $employee->deleted_at ? 
                    $employee->deleted_at->format('d/m/Y H:i') : 
                    '';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Criar novo colaborador
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:50|unique:employees,document',
            'company_id' => 'required|exists:companies,id',
        ], [
            'name.required' => 'O nome do colaborador é obrigatório.',
            'document.required' => 'O documento é obrigatório.',
            'document.unique' => 'Este documento já está registado.',
            'company_id.required' => 'A empresa é obrigatória.',
            'company_id.exists' => 'A empresa selecionada não existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Employee::create([
                'name' => $request->name,
                'document' => $request->document,
                'company_id' => $request->company_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Colaborador criado com sucesso!',
                'data' => $employee
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar colaborador
     */
    public function edit(Employee $employee)
    {
        return response()->json([
            'success' => true,
            'data' => $employee->load('company')
        ]);
    }

    /**
     * Atualizar colaborador
     */
    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:50|unique:employees,document,' . $employee->id,
            'company_id' => 'required|exists:companies,id',
        ], [
            'name.required' => 'O nome do colaborador é obrigatório.',
            'document.required' => 'O documento é obrigatório.',
            'document.unique' => 'Este documento já está registado.',
            'company_id.required' => 'A empresa é obrigatória.',
            'company_id.exists' => 'A empresa selecionada não existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee->update([
                'name' => $request->name,
                'document' => $request->document,
                'company_id' => $request->company_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Colaborador atualizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar colaborador (soft delete)
     */
    public function destroy(Employee $employee)
    {
        // Verificar permissão de admin
        if (!$this->authorize('delete', $employee)) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar colaboradores.'
            ], 403);
        }

        try {
            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Colaborador eliminado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar colaborador eliminado
     */
    public function restore($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem restaurar colaboradores.'
            ], 403);
        }

        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->restore();

            return response()->json([
                'success' => true,
                'message' => 'Colaborador restaurado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente colaborador
     */
    public function forceDelete($id)
    {
        // Verificar permissão de admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem eliminar permanentemente colaboradores.'
            ], 403);
        }

        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Colaborador eliminado permanentemente!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar colaborador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar colaboradores para select2
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $employees = Employee::select('id', 'name as text')
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $employees
        ]);
    }

    /**
     * Buscar colaboradores por empresa
     */
    public function byCompany($companyId)
    {
        $employees = Employee::where('company_id', $companyId)
            ->select('id', 'name', 'document')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }
}