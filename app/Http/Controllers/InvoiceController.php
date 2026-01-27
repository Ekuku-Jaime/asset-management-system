<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    /**
     * Diretório de upload
     */
    private $uploadPath = 'invoices/documents';
    
    /**
     * Tipos de ficheiros permitidos
     */
    private $allowedMimes = [
        'pdf', 'jpg', 'jpeg', 'png', 
        'doc', 'docx', 'xls', 'xlsx'
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
        $invoices = Invoice::select(['id', 'number', 'date', 'status', 'created_at', 'deleted_at']);
        
        return DataTables::of($invoices)
            ->addIndexColumn()
            ->addColumn('actions', function($invoice) {
                return '';
            })
            ->editColumn('date', function($invoice) {
                return $invoice->date;
            })
            ->editColumn('status', function($invoice) {
                $badgeClass = $invoice->status == 'completo' ? 'bg-success' : 'bg-warning';
                $icon = $invoice->status == 'completo' ? 'fa-check-circle' : 'fa-times-circle';
                $text = $invoice->status == 'completo' ? 'completo' : 'incompleto';
                
                return $text;
            })
            ->editColumn('created_at', function($invoice) {
                return $invoice->created_at;
            })
            ->editColumn('deleted_at', function($invoice) {
                return $invoice->deleted_at ? 'Eliminada':'';
                    // '<span class="badge bg-danger">Eliminada</span>' : 
                    // '<span class="badge bg-success">Ativa</span>';
            })
            ->rawColumns(['actions', 'deleted_at', 'status'])
            ->make(true);
    }

    /**
     * Retornar dados eliminados para DataTable
     */
    public function dataTrashed()
    {
        $invoices = Invoice::onlyTrashed()
            ->select(['id', 'number', 'date', 'status', 'created_at', 'deleted_at']);
        
        return DataTables::of($invoices)
            ->addIndexColumn()
            ->addColumn('actions', function($invoice) {
                return '';
            })
            ->editColumn('date', function($invoice) {
                return $invoice->date;
            })
            ->editColumn('status', function($invoice) {
                $badgeClass = $invoice->status == 'completo' ? 'bg-success' : 'bg-warning';
                $icon = $invoice->status == 'completo' ? 'fa-check-circle' : 'fa-times-circle';
                $text = $invoice->status == 'completo' ? 'Completo' : 'Incompleto';
                
                return $text;
            })
            ->editColumn('created_at', function($invoice) {
                return $invoice->created_at;
            })
            ->editColumn('deleted_at', function($invoice) {
                return $invoice->deleted_at ? 
                   $invoice->deleted_at : 
                    '';
            })
            ->rawColumns(['actions', 'status'])
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
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:' . implode(',', $this->allowedMimes) . 
                           '|max:' . $this->maxFileSize
        ], [
            'number.required' => 'O número da fatura é obrigatório.',
            'number.unique' => 'Este número de fatura já está registado.',
            'date.required' => 'A data da fatura é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da fatura não pode ser no futuro.',
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

            $invoice = Invoice::create([
                'number' => $request->number,
                'date' => $request->date,
            ]);

            // Processar upload de documentos
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $this->storeDocument($file, $invoice);
                }
                $invoice->updateStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fatura criada com sucesso!',
                'data' => $invoice->load('documents')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar fatura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para armazenar documento
     */
    private function storeDocument($file, $invoice)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(10) . '.' . $extension;
        $path = $file->storeAs($this->uploadPath, $filename, 'public');

        return InvoiceDocument::create([
            'invoice_id' => $invoice->id,
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path
        ]);
    }

    /**
     * Editar fatura
     */
    public function edit(Invoice $invoice)
    {
        return response()->json([
            'success' => true,
            'data' => $invoice->load('documents')
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
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:' . implode(',', $this->allowedMimes) . 
                           '|max:' . $this->maxFileSize
        ], [
            'number.required' => 'O número da fatura é obrigatório.',
            'number.unique' => 'Este número de fatura já está registado.',
            'date.required' => 'A data da fatura é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.before_or_equal' => 'A data da fatura não pode ser no futuro.',
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

            $invoice->update([
                'number' => $request->number,
                'date' => $request->date,
            ]);

            // Processar upload de novos documentos
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $this->storeDocument($file, $invoice);
                }
            }
            
            $invoice->updateStatus();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fatura atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
            
            // Eliminar documentos primeiro
            foreach ($invoice->documents as $document) {
                Storage::disk('public')->delete($document->path);
                $document->delete();
            }
            
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

    /**
     * Upload de documentos para uma fatura existente
     */
    public function uploadDocuments(Request $request, Invoice $invoice)
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
                $document = $this->storeDocument($file, $invoice);
                $uploadedFiles[] = $document;
            }
            
            $invoice->updateStatus();

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
    public function removeDocument(InvoiceDocument $document)
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
            
            // Atualizar status da fatura
            $document->invoice->updateStatus();

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
    public function downloadDocument(InvoiceDocument $document)
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
     * Listar documentos de uma fatura
     */
    public function listDocuments(Invoice $invoice)
    {
        return response()->json([
            'success' => true,
            'data' => $invoice->documents
        ]);
    }
}