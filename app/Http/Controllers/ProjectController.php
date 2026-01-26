<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
     * Retornar dados para DataTable
     */
    public function data()
    {
        $projects = Project::select(['id', 'name', 'created_at', 'deleted_at']);
        
        return DataTables::of($projects)
            ->addIndexColumn()
            ->addColumn('actions', function($project) {
                return '';
            })
            ->editColumn('created_at', function($project) {
                return $project->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($project) {
                return $project->deleted_at ? 
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
        $projects = Project::onlyTrashed()->select(['id', 'name', 'created_at', 'deleted_at']);
        
        return DataTables::of($projects)
            ->addIndexColumn()
            ->addColumn('actions', function($project) {
                return '';
            })
            ->editColumn('created_at', function($project) {
                return $project->created_at->format('d/m/Y H:i');
            })
            ->editColumn('deleted_at', function($project) {
                return $project->deleted_at ? 
                    $project->deleted_at->format('d/m/Y H:i') : 
                    '';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Criar novo projeto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects,name',
        ], [
            'name.required' => 'O nome do projeto é obrigatório.',
            'name.unique' => 'Este nome de projeto já está registado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $project = Project::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Projeto criado com sucesso!',
                'data' => $project
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar projeto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar projeto
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
        ], [
            'name.required' => 'O nome do projeto é obrigatório.',
            'name.unique' => 'Este nome de projeto já está registado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $project->update([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Projeto atualizado com sucesso!'
            ]);

        } catch (\Exception $e) {
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
        
        $projects = Project::select('id', 'name as text')
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get();
            
        return response()->json([
            'results' => $projects
        ]);
    }
}