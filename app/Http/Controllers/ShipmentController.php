<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ShipmentController extends Controller
{
    /**
     * Diretório de upload
     */
    private $uploadPath = 'shipments/documents';
    
    /**
     * Tipos de ficheiros permitidos
     */
    private $allowedMimes = [
        'pdf', 'jpg', 'jpeg', 'png', 
        'doc', 'docx'
    ];
    
    /**
     * Tamanho máximo (10MB)
     */
    private $maxFileSize = 10240;

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
    public function data(Request $request)
    {
        $query = Shipment::withCount('documents')
            ->select(['id', 'guide', 'date', 'status', 'created_at', 'deleted_at']);
        
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
            ->editColumn('status', function($shipment) {
                $badgeClass = $shipment->status == 'completo' ? 'bg-success' : 'bg-warning';
                $icon = $shipment->status == 'completo' ? 'fa-check-circle' : 'fa-times-circle';
                $text = $shipment->status == 'completo' ? 'Completo' : 'Incompleto';
                
                return '<span class="badge ' . $badgeClass . '">
                            <i class="fas ' . $icon . ' me-1"></i>' . $text . '
                        </span>';
            })
            ->editColumn('created_at', function($shipment) {
                return Carbon::parse($shipment->created_at);
            })
            ->editColumn('deleted_at', function($shipment) {
                return $shipment->deleted_at ? 
                    '<span class="badge bg-danger">Eliminada</span>' : 
                    '<span class="badge bg-success">Ativa</span>';
            })
            ->rawColumns(['actions', 'deleted_at', 'status'])
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed(Request $request)
    {
        return DataTables::eloquent(Shipment::onlyTrashed()
            ->withCount('documents')
            ->select(['id', 'guide', 'date', 'status', 'created_at', 'deleted_at']))
            ->addColumn('actions', '')
            ->editColumn('guide', function($shipment) {
                return strtoupper($shipment->guide);
            })
            ->editColumn('status', function($shipment) {
                $badgeClass = $shipment->status == 'completo' ? 'bg-success' : 'bg-warning';
                $icon = $shipment->status == 'completo' ? 'fa-check-circle' : 'fa-times-circle';
                $text = $shipment->status == 'completo' ? 'Completo' : 'Incompleto';
                
                return '<span class="badge ' . $badgeClass . '">
                            <i class="fas ' . $icon . ' me-1"></i>' . $text . '
                        </span>';
            })
            ->rawColumns(['actions', 'status'])
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
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:' . implode(',', $this->allowedMimes) . 
                           '|max:' . $this->maxFileSize
        ], [
            'guide.required' => 'O guia da remessa é obrigatório.',
            'guide.unique' => 'Este guia já está registado.',
            'guide.max' => 'O guia não pode ter mais de 100 caracteres.',
            'date.required' => 'A data da remessa é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da remessa não pode ser no futuro.',
            'documents.*.file' => 'O ficheiro deve ser válido.',
            'documents.*.mimes' => 'Tipo de ficheiro não permitido. Tipos permitidos: ' . 
                                   implode(', ', $this->allowedMimes),
            'documents.*.max' => 'O ficheiro não pode exceder :max KB.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $shipment = Shipment::create([
                'guide' => $request->guide,
                'date' => $request->date,
            ]);

            // Processar upload de documentos
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $this->storeDocument($file, $shipment);
                }
                $shipment->updateStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remessa criada com sucesso!',
                'data' => $shipment->load('documents')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar remessa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para armazenar documento
     */
    private function storeDocument($file, $shipment)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(10) . '.' . $extension;
        $path = $file->storeAs($this->uploadPath, $filename, 'public');

        return ShipmentDocument::create([
            'shipment_id' => $shipment->id,
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path
        ]);
    }

    /**
     * Editar remessa
     */
    public function edit(Shipment $shipment)
    {
        return response()->json([
            'success' => true,
            'data' => $shipment->load('documents')
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
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:' . implode(',', $this->allowedMimes) . 
                           '|max:' . $this->maxFileSize
        ], [
            'guide.required' => 'O guia da remessa é obrigatório.',
            'guide.unique' => 'Este guia já está registado.',
            'guide.max' => 'O guia não pode ter mais de 100 caracteres.',
            'date.required' => 'A data da remessa é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da remessa não pode ser no futuro.',
            'documents.*.file' => 'O ficheiro deve ser válido.',
            'documents.*.mimes' => 'Tipo de ficheiro não permitido.',
            'documents.*.max' => 'O ficheiro não pode exceder :max KB.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $shipment->update([
                'guide' => $request->guide,
                'date' => $request->date,
            ]);

            // Processar upload de novos documentos
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $this->storeDocument($file, $shipment);
                }
            }
            
            $shipment->updateStatus();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remessa atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
            
            // Eliminar documentos primeiro
            foreach ($shipment->documents as $document) {
                Storage::disk('public')->delete($document->path);
                $document->delete();
            }
            
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

    /**
     * Upload de documentos para uma remessa existente
     */
    public function uploadDocuments(Request $request, Shipment $shipment)
    {
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array',
            'documents.*' => 'required|file|mimes:' . implode(',', $this->allowedMimes) . 
                           '|max:' . $this->maxFileSize
        ], [
            'documents.required' => 'Selecione pelo menos um ficheiro.',
            'documents.*.required' => 'O ficheiro é obrigatório.',
            'documents.*.file' => 'O ficheiro deve ser válido.',
            'documents.*.mimes' => 'Tipo de ficheiro não permitido.',
            'documents.*.max' => 'O ficheiro não pode exceder :max KB.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedFiles = [];
            
            foreach ($request->file('documents') as $file) {
                $document = $this->storeDocument($file, $shipment);
                $uploadedFiles[] = $document;
            }
            
            $shipment->updateStatus();

            return response()->json([
                'success' => true,
                'message' => 'Documentos carregados com sucesso!',
                'data' => $uploadedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar documentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover documento
     */
    public function removeDocument(ShipmentDocument $document)
    {
        try {
            // Verificar permissão
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas administradores podem remover documentos.'
                ], 403);
            }

            // Remover ficheiro do storage
            Storage::disk('public')->delete($document->path);
            
            // Remover do banco de dados
            $document->delete();
            
            // Atualizar status da remessa
            $document->shipment->updateStatus();

            return response()->json([
                'success' => true,
                'message' => 'Documento removido com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download documento
     */
    public function downloadDocument(ShipmentDocument $document)
    {
        if (!Storage::disk('public')->exists($document->path)) {
            abort(404, 'Ficheiro não encontrado.');
        }

        return Storage::disk('public')->download(
            $document->path, 
            $document->original_name
        );
    }

    /**
     * Listar documentos de uma remessa
     */
    public function listDocuments(Shipment $shipment)
    {
        return response()->json([
            'success' => true,
            'data' => $shipment->documents
        ]);
    }
}