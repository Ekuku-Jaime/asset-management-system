<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    /**
     * Lista de províncias de Maputo
     */
    private function getProvinces()
    {
        return [
            'Maputo Cidade' => 'Maputo Cidade',
            'Maputo Província' => 'Maputo Província',
            'Gaza' => 'Gaza',
            'Inhambane' => 'Inhambane',
            'Sofala' => 'Sofala',
            'Manica' => 'Manica',
            'Tete' => 'Tete',
            'Zambézia' => 'Zambézia',
            'Nampula' => 'Nampula',
            'Cabo Delgado' => 'Cabo Delgado',
            'Niassa' => 'Niassa'
        ];
    }

    /**
     * Mostrar lista de empresas
     */
    public function index()
    {
        $provinces = $this->getProvinces();
        return view('companies.index', compact('provinces'));
    }

    /**
     * Retornar dados para DataTable
     */
    public function data()
    {
        $companies = Company::select(['id', 'name', 'province', 'created_at', 'deleted_at']);
        
        return DataTables::of($companies)
            ->addIndexColumn()
            ->addColumn('actions', function($company) {
                return '';
            })
            ->editColumn('created_at', function($company) {
                return $company->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($company) {
                return $company->deleted_at ? 
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
        $companies = Company::onlyTrashed()->select(['id', 'name', 'province', 'created_at', 'deleted_at']);
        
        return DataTables::of($companies)
            ->addIndexColumn()
            ->addColumn('actions', function($company) {
                return '';
            })
            ->editColumn('created_at', function($company) {
                return $company->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($company) {
                return $company->deleted_at ? 
                    $company->deleted_at->format('d/m/Y H:i') : 
                    '';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Criar nova empresa
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:companies,name',
            'province' => 'required|string|max:255',
        ], [
            'name.required' => 'O nome da empresa é obrigatório.',
            'name.unique' => 'Este nome de empresa já está registado.',
            'province.required' => 'A província é obrigatória.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company = Company::create([
                'name' => $request->name,
                'province' => $request->province,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empresa criada com sucesso!',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar empresa
     */
    public function edit(Company $company)
    {
        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Atualizar empresa
     */
    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'province' => 'required|string|max:255',
        ], [
            'name.required' => 'O nome da empresa é obrigatório.',
            'name.unique' => 'Este nome de empresa já está registado.',
            'province.required' => 'A província é obrigatória.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company->update([
                'name' => $request->name,
                'province' => $request->province,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empresa atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar empresa (soft delete)
     */
    public function destroy(Company $company)
    {
        try {
            $company->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empresa eliminada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar empresa eliminada
     */
    public function restore($id)
    {
        try {
            $company = Company::withTrashed()->findOrFail($id);
            $company->restore();

            return response()->json([
                'success' => true,
                'message' => 'Empresa restaurada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar permanentemente empresa
     */
    public function forceDelete($id)
    {
        try {
            $company = Company::withTrashed()->findOrFail($id);
            $company->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Empresa eliminada permanentemente!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar empresas para select2
     */
    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $companies = Company::select('id', 'name as text')
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $companies
        ]);
    }

    /**
     * Obter lista de províncias (API)
     */
    public function provinces()
    {
        return response()->json([
            'provinces' => $this->getProvinces()
        ]);
    }
}