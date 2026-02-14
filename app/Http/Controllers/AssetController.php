<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDocument;
use App\Models\Request as RequestModel;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Employee;
use App\Models\Shipment;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AssetController extends Controller
{
    const ASSET_STATUSES = [
        'disponivel' => 'Disponível',
        'atribuido' => 'Atribuído',
        'manutencao' => 'Em Manutenção',
        'inoperacional' => 'Inoperacional',
        'abatido' => 'Abatido'
    ];

    const CATEGORIES = [
        'hardware' => 'Hardware',
        'software' => 'Software',
        'equipamento' => 'Equipamento',
        'mobiliario' => 'Mobiliário',
        'veiculo' => 'Veículo',
        'outro' => 'Outro'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carregar todos os dados para os dropdowns
        $data = [
            'asset_statuses' => self::ASSET_STATUSES,
            'categories' => self::CATEGORIES,
            'suppliers' => Supplier::all(),
            'invoices' => Invoice::all(),
            'requests' => RequestModel::all(),
            'shipments' => Shipment::all(),
            'employees' => Employee::with('company')->get()
        ];

        // Passar diretamente para a view (não via JSON)
        return view('assets.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Este método pode ser removido pois já passamos os dados no index()
        // Ou mantido para outras funcionalidades
        return response()->json([
            'success' => true,
            'message' => 'Use o modal para criar novo activo'
        ]);
    }

    /**
     * Datatable data
     */
    public function datatable(HttpRequest $request)
{
    try {
        $query = Asset::with([
            'employee.company',
            'supplier',
            'invoice',
            'request',
            'request.project',
            'shipment',
            'documents'
        ]);

        // ... resto dos filtros existentes ...

        return DataTables::eloquent($query)
            ->addColumn('checkbox', function($asset) {
                return '<input type="checkbox" class="select-checkbox asset-checkbox" data-id="' . $asset->id . '">';
            })
            ->addColumn('status_badge', function($asset) {
                $badgeClass = [
                    'disponivel' => 'badge-disponivel',
                    'atribuido' => 'badge-atribuido',
                    'manutencao' => 'badge-manutencao',
                    'inoperacional' => 'badge-inoperacional',
                    'abatido' => 'badge-abatido'
                ][$asset->asset_status] ?? 'badge-abatido';
                
                return '<span class="status-badge ' . $badgeClass . '">' . 
                       (self::ASSET_STATUSES[$asset->asset_status] ?? $asset->asset_status) . '</span>';
            })
            ->addColumn('category_badge', function($asset) {
                return '<span class="badge bg-light text-dark">' . 
                       (self::CATEGORIES[$asset->category] ?? $asset->category) . '</span>';
            })
            ->addColumn('warranty_indicator', function($asset) {
                if (!$asset->warranty_expiry) {
                    return 'N/A';
                }
                
                try {
                    $expiry = new \DateTime($asset->warranty_expiry);
                    $today = new \DateTime();
                    
                    if ($expiry < $today) {
                        return '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Expirada</span>';
                    }
                    
                    $interval = $today->diff($expiry);
                    $days = $interval->days;
                    
                    if ($days <= 30) {
                        return '<span class="text-warning"><i class="fas fa-clock me-1"></i>' . $days . 'd</span>';
                    }
                    
                    return $expiry->format('d/m/Y');
                } catch (\Exception $e) {
                    return 'N/A';
                }
            })
            ->addColumn('documents_count', function($asset) {
                return $asset->documents ? $asset->documents->count() : 0;
            })
            ->addColumn('actions', function($asset) {
                $buttons = '<div class="action-buttons">';
                $buttons .= '<button class="btn-action view" onclick="showQuickView(' . $asset->id . ')" title="Ver detalhes"><i class="fas fa-eye"></i></button>';
                $buttons .= '<button class="btn-action edit" onclick="showEditForm(' . $asset->id . ')" title="Editar"><i class="fas fa-edit"></i></button>';
                
                if ($asset->asset_status === 'disponivel') {
                    $buttons .= '<button class="btn-action assign" onclick="showAssignModal(' . $asset->id . ')" title="Atribuir"><i class="fas fa-user-tag"></i></button>';
                }
                
                if ($asset->asset_status === 'atribuido') {
                    $buttons .= '<button class="btn-action unassign" onclick="removeAssignment(' . $asset->id . ')" title="Remover atribuição"><i class="fas fa-user-times"></i></button>';
                }
                
                if (in_array($asset->asset_status, ['disponivel', 'atribuido', 'inoperacional'])) {
                    $buttons .= '<button class="btn-action maintenance" onclick="showMaintenanceModal(' . $asset->id . ')" title="Marcar para manutenção"><i class="fas fa-tools"></i></button>';
                }

                if ($asset->asset_status === 'manutencao') {
                    $buttons .= '<button class="btn-action complete-maintenance" onclick="showCompleteMaintenanceModal(' . $asset->id . ')" title="Concluir manutenção"><i class="fas fa-check-circle"></i></button>';
                }
                
                if (in_array($asset->asset_status, ['disponivel', 'atribuido', 'manutencao'])) {
                    $buttons .= '<button class="btn-action inoperational" onclick="markInoperational(' . $asset->id . ')" title="Marcar como inoperacional"><i class="fas fa-exclamation-triangle"></i></button>';
                }
                
                if (in_array($asset->asset_status, ['disponivel', 'atribuido', 'manutencao', 'inoperacional'])) {
                    $buttons .= '<button class="btn-action writeoff" onclick="writeOffAsset(' . $asset->id . ')" title="Abater activo"><i class="fas fa-trash-alt"></i></button>';
                }
                
                $buttons .= '<button class="btn-action delete" onclick="confirmDelete(' . $asset->id . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $buttons .= '</div>';
                
                return $buttons;
            })
            // Colunas existentes (mantendo exatamente os mesmos nomes)
            ->addColumn('company_name', function($asset) {
                if ($asset->employee && $asset->employee->company) {
                    return e($asset->employee->company->name);
                }
                return 'N/A';
            })
            ->addColumn('supplier_info', function($asset) {
                return $asset->supplier ? e($asset->supplier->name) : 'N/A';
            })
            ->addColumn('invoice_number', function($asset) {
                return $asset->invoice ? e($asset->invoice->number) : 'N/A';
            })
            ->addColumn('request_code', function($asset) {
                return $asset->request ? e($asset->request->code) : 'N/A';
            })
            ->addColumn('request_type', function($asset) {
                return $asset->request ? e($asset->request->type ?? 'N/A') : 'N/A';
            })
            ->addColumn('request_date', function($asset) {
                return $asset->request ? e($asset->request->date ?? 'N/A') : 'N/A';
            })
            ->addColumn('project_name', function($asset) {
                return $asset->request && $asset->request->project ? e($asset->request->project->name ?? 'N/A') : 'N/A';
            })
            ->addColumn('employee_name', function($asset) {
                return $asset->employee ? e($asset->employee->name ?? 'N/A') : 'N/A';         
            })
            ->addColumn('shipment_tracking', function($asset) {
                return $asset->shipment ? e($asset->shipment->guide) : 'N/A';
            })
            ->addColumn('shipment_date', function($asset) {
                return $asset->shipment ? e($asset->shipment->date) : 'N/A';
            })
            ->addColumn('process_status', function($asset) {
                return $asset->process_status ? e($asset->process_status) : 'N/A';
            })
            
            // FILTROS ADICIONAIS - mantendo os existentes e adicionando os que faltam
            // Filtros existentes
            ->filterColumn('company_name', function($query, $keyword) {
                $query->whereHas('employee.company', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('invoice_number', function($query, $keyword) {
                $query->whereHas('invoice', function($q) use ($keyword) {
                    $q->where('number', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_code', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('shipment_tracking', function($query, $keyword) {
                $query->whereHas('shipment', function($q) use ($keyword) {
                    $q->where('guide', 'like', "%{$keyword}%");
                });
            })
            
            // NOVOS FILTROS - para as colunas que já existem mas não tinham filtro
            ->filterColumn('supplier_info', function($query, $keyword) {
                $query->whereHas('supplier', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_type', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('type', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_date', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('date', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('project_name', function($query, $keyword) {
                $query->whereHas('request.project', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('employee_name', function($query, $keyword) {
                $query->whereHas('employee', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('shipment_date', function($query, $keyword) {
                $query->whereHas('shipment', function($q) use ($keyword) {
                    $q->where('date', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('process_status', function($query, $keyword) {
                $query->where('process_status', 'like', "%{$keyword}%");
            })
            
            // FILTROS PARA CAMPOS DO ASSET (para pesquisa global funcionar com todos os campos)
            ->filterColumn('asset_tag', function($query, $keyword) {
                $query->orWhere('asset_tag', 'like', "%{$keyword}%");
            })
            ->filterColumn('serial_number', function($query, $keyword) {
                $query->orWhere('serial_number', 'like', "%{$keyword}%");
            })
            ->filterColumn('category', function($query, $keyword) {
                $query->orWhere('category', 'like', "%{$keyword}%");
            })
            ->filterColumn('brand', function($query, $keyword) {
                $query->orWhere('brand', 'like', "%{$keyword}%");
            })
            ->filterColumn('model', function($query, $keyword) {
                $query->orWhere('model', 'like', "%{$keyword}%");
            })
            ->filterColumn('description', function($query, $keyword) {
                $query->orWhere('description', 'like', "%{$keyword}%");
            })
            ->filterColumn('acquisition_date', function($query, $keyword) {
                $query->orWhere('acquisition_date', 'like', "%{$keyword}%");
            })
            ->filterColumn('asset_status', function($query, $keyword) {
                $query->orWhere('asset_status', 'like', "%{$keyword}%");
            })
            ->filterColumn('location', function($query, $keyword) {
                $query->orWhere('location', 'like', "%{$keyword}%");
            })
            ->filterColumn('notes', function($query, $keyword) {
                $query->orWhere('notes', 'like', "%{$keyword}%");
            })
            ->filterColumn('base_value', function($query, $keyword) {
                $query->orWhere('base_value', 'like', "%{$keyword}%");
            })
            ->filterColumn('iva_value', function($query, $keyword) {
                $query->orWhere('iva_value', 'like', "%{$keyword}%");
            })
            ->filterColumn('total_value', function($query, $keyword) {
                $query->orWhere('total_value', 'like', "%{$keyword}%");
            })
            ->filterColumn('warranty_expiry', function($query, $keyword) {
                $query->orWhere('warranty_expiry', 'like', "%{$keyword}%");
            })
            
            // Ordenações existentes (mantendo exatamente como estão)
            ->orderColumn('base_value', 'base_value $1')
            ->orderColumn('iva_value', 'iva_value $1')
            ->orderColumn('total_value', 'total_value $1')
            ->orderColumn('company_name', 'employee.company.name $1')
            ->orderColumn('invoice_number', 'invoice.number $1')
            ->orderColumn('request_code', 'request.code $1')
            ->orderColumn('shipment_tracking', 'shipment.guide $1')
            
            // Novas ordenações para as outras colunas
            ->orderColumn('supplier_info', function($query, $order) {
                $query->leftJoin('suppliers', 'assets.supplier_id', '=', 'suppliers.id')
                      ->orderBy('suppliers.name', $order);
            })
            ->orderColumn('request_type', function($query, $order) {
                $query->leftJoin('requests', 'assets.request_id', '=', 'requests.id')
                      ->orderBy('requests.type', $order);
            })
            ->orderColumn('request_date', function($query, $order) {
                $query->leftJoin('requests', 'assets.request_id', '=', 'requests.id')
                      ->orderBy('requests.date', $order);
            })
            ->orderColumn('project_name', function($query, $order) {
                $query->leftJoin('requests', 'assets.request_id', '=', 'requests.id')
                      ->leftJoin('projects', 'requests.project_id', '=', 'projects.id')
                      ->orderBy('projects.name', $order);
            })
            ->orderColumn('employee_name', function($query, $order) {
                $query->leftJoin('employees', 'assets.employee_id', '=', 'employees.id')
                      ->orderBy('employees.name', $order);
            })
            ->orderColumn('shipment_date', function($query, $order) {
                $query->leftJoin('shipments', 'assets.shipment_id', '=', 'shipments.id')
                      ->orderBy('shipments.date', $order);
            })
            ->orderColumn('process_status', 'process_status $1')
            ->orderColumn('asset_tag', 'asset_tag $1')
            ->orderColumn('serial_number', 'serial_number $1')
            ->orderColumn('category', 'category $1')
            ->orderColumn('brand', 'brand $1')
            ->orderColumn('model', 'model $1')
            ->orderColumn('acquisition_date', 'acquisition_date $1')
            ->orderColumn('asset_status', 'asset_status $1')
            ->orderColumn('location', 'location $1')
            ->orderColumn('warranty_expiry', 'warranty_expiry $1')
            
            ->rawColumns([
                'checkbox', 
                'status_badge', 
                'category_badge', 
                'warranty_indicator', 
                'actions',
                'company_name',
                'supplier_info',
                'invoice_number',
                'request_code',
                'shipment_tracking'
            ])
            ->toJson();

    } catch (\Exception $e) {
        \Log::error('Erro no DataTables: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'draw' => $request->get('draw', 0),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Erro ao carregar dados'
        ], 500);
    }
}
    private function applyQuickFilter($query, $filter)
    {
        switch($filter) {
            case 'disponivel':
                return $query->where('asset_status', 'disponivel');
            case 'atribuido':
                return $query->where('asset_status', 'atribuido');
            case 'inoperacional':
                return $query->where('asset_status', 'inoperacional');
            case 'abatido':
                return $query->where('asset_status', 'abatido');
            case 'manutencao':
                return $query->where('asset_status', 'manutencao');
            case 'garantia':
                $thirtyDaysFromNow = now()->addDays(30)->format('Y-m-d');
                return $query->whereNotNull('warranty_expiry')
                    ->where('warranty_expiry', '<=', $thirtyDaysFromNow)
                    ->where('warranty_expiry', '>=', now()->format('Y-m-d'));
            default:
                return $query;
        }
    }

    public function stats()
    {
        $stats = [
            'total' => Asset::count(),
            'disponivel' => Asset::where('asset_status', 'disponivel')->count(),
            'atribuido' => Asset::where('asset_status', 'atribuido')->count(),
            'inoperacional' => Asset::where('asset_status', 'inoperacional')->count(),
            'manutencao' => Asset::where('asset_status', 'manutencao')->count(),
            'abatido' => Asset::where('asset_status', 'abatido')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    //Excel export
    public function export(HttpRequest $request)
{
    try {
        $query = Asset::with([
            'employee.company', 
            'supplier', 
            'invoice', 
            'request',
            'request.project',
            'shipment',
            'documents'
        ]);

        // APLICAR OS MESMOS FILTROS DO DATATABLE
        // ============================================
        
        // Filtro rápido (status)
        if ($request->filled('quick_filter') && $request->quick_filter !== 'all') {
            $query = $this->applyQuickFilter($query, $request->quick_filter);
        }

        // Filtros de coluna individuais (advanced filters)
        if ($request->filled('filters')) {
            $filters = is_array($request->filters) ? $request->filters : json_decode($request->filters, true);
            
            foreach ($filters as $column => $value) {
                if (empty($value)) continue;
                
                switch ($column) {
                    case 'company_name':
                        $query->whereHas('employee.company', function($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'supplier_info':
                        $query->whereHas('supplier', function($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'invoice_number':
                        $query->whereHas('invoice', function($q) use ($value) {
                            $q->where('number', 'like', "%{$value}%");
                        });
                        break;
                    case 'request_code':
                        $query->whereHas('request', function($q) use ($value) {
                            $q->where('code', 'like', "%{$value}%");
                        });
                        break;
                    case 'request_type':
                        $query->whereHas('request', function($q) use ($value) {
                            $q->where('type', 'like', "%{$value}%");
                        });
                        break;
                    case 'request_date':
                        $query->whereHas('request', function($q) use ($value) {
                            $q->where('date', 'like', "%{$value}%");
                        });
                        break;
                    case 'project_name':
                        $query->whereHas('request.project', function($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'employee_name':
                        $query->whereHas('employee', function($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        });
                        break;
                    case 'shipment_tracking':
                        $query->whereHas('shipment', function($q) use ($value) {
                            $q->where('guide', 'like', "%{$value}%");
                        });
                        break;
                    case 'shipment_date':
                        $query->whereHas('shipment', function($q) use ($value) {
                            $q->where('date', 'like', "%{$value}%");
                        });
                        break;
                    case 'process_status':
                        $query->where('process_status', 'like', "%{$value}%");
                        break;
                    case 'asset_tag':
                        $query->where('asset_tag', 'like', "%{$value}%");
                        break;
                    case 'serial_number':
                        $query->where('serial_number', 'like', "%{$value}%");
                        break;
                    case 'category':
                        $query->where('category', 'like', "%{$value}%");
                        break;
                    case 'brand':
                        $query->where('brand', 'like', "%{$value}%");
                        break;
                    case 'model':
                        $query->where('model', 'like', "%{$value}%");
                        break;
                    case 'description':
                        $query->where('description', 'like', "%{$value}%");
                        break;
                    case 'acquisition_date':
                        $query->where('acquisition_date', 'like', "%{$value}%");
                        break;
                    case 'asset_status':
                        $query->where('asset_status', 'like', "%{$value}%");
                        break;
                    case 'location':
                        $query->where('location', 'like', "%{$value}%");
                        break;
                    case 'base_value':
                        $query->where('base_value', 'like', "%{$value}%");
                        break;
                    case 'iva_value':
                        $query->where('iva_value', 'like', "%{$value}%");
                        break;
                    case 'total_value':
                        $query->where('total_value', 'like', "%{$value}%");
                        break;
                    case 'warranty_expiry':
                        $query->where('warranty_expiry', 'like', "%{$value}%");
                        break;
                }
            }
        }

        // Filtro de pesquisa global (search)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                // Campos diretos do Asset
                $q->where('asset_tag', 'like', "%{$searchTerm}%")
                  ->orWhere('serial_number', 'like', "%{$searchTerm}%")
                  ->orWhere('brand', 'like', "%{$searchTerm}%")
                  ->orWhere('model', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%")
                  ->orWhere('asset_status', 'like', "%{$searchTerm}%")
                  ->orWhere('process_status', 'like', "%{$searchTerm}%")
                  ->orWhere('base_value', 'like', "%{$searchTerm}%")
                  ->orWhere('total_value', 'like', "%{$searchTerm}%")
                  // Relacionamentos
                  ->orWhereHas('employee', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('employee.company', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('supplier', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('invoice', function($q) use ($searchTerm) {
                      $q->where('number', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('request', function($q) use ($searchTerm) {
                      $q->where('code', 'like', "%{$searchTerm}%")
                        ->orWhere('type', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('request.project', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('code', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('shipment', function($q) use ($searchTerm) {
                      $q->where('guide', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Ordenação (se vier da request)
        if ($request->filled('order_column')) {
            $orderDir = $request->get('order_dir', 'asc');
            $orderColumn = $request->order_column;
            
            // Mapear colunas de ordenação
            $orderMapping = [
                'company_name' => 'employee.company.name',
                'supplier_info' => 'supplier.name',
                'invoice_number' => 'invoice.number',
                'request_code' => 'request.code',
                'request_date' => 'request.date',
                'project_name' => 'request.project.name',
                'employee_name' => 'employee.name',
                'shipment_tracking' => 'shipment.guide',
                'shipment_date' => 'shipment.date',
            ];
            
            if (isset($orderMapping[$orderColumn])) {
                $query->orderBy($orderMapping[$orderColumn], $orderDir);
            } else {
                $query->orderBy($orderColumn ?? 'id', $orderDir);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        // Buscar todos os registros filtrados
        $assets = $query->get();

        // Mapear dados para exportação
        $data = $assets->map(function($asset) {
            // Badge de garantia igual ao datatable
            $warrantyText = 'N/A';
            if ($asset->warranty_expiry) {
                try {
                    $expiry = new \DateTime($asset->warranty_expiry);
                    $today = new \DateTime();
                    
                    if ($expiry < $today) {
                        $warrantyText = 'Expirada';
                    } else {
                        $interval = $today->diff($expiry);
                        $days = $interval->days;
                        
                        if ($days <= 30) {
                            $warrantyText = $days . ' dias restantes';
                        } else {
                            $warrantyText = $expiry->format('d/m/Y');
                        }
                    }
                } catch (\Exception $e) {
                    $warrantyText = 'N/A';
                }
            }

            return [
                'ID' => $asset->id,
                'Asset Tag' => $asset->asset_tag,
                'Nº Série' => $asset->serial_number,
                'Categoria' => self::CATEGORIES[$asset->category] ?? $asset->category,
                'Marca' => $asset->brand,
                'Modelo' => $asset->model,
                'Descrição' => $asset->description,
                'Estado' => self::ASSET_STATUSES[$asset->asset_status] ?? $asset->asset_status,
                'Status Processo' => $asset->process_status ?? 'N/A',
                'Localização' => $asset->location,
                'Valor Base (MT)' => number_format($asset->base_value, 2, ',', '.'),
                'IVA (MT)' => number_format($asset->iva_value, 2, ',', '.'),
                'Valor Total (MT)' => number_format($asset->total_value, 2, ',', '.'),
                'Data Aquisição' => $asset->acquisition_date ? 
                    \Carbon\Carbon::parse($asset->acquisition_date)->format('d/m/Y') : 'N/A',
                'Garantia' => $warrantyText,
                'Fornecedor' => $asset->supplier->name ?? 'N/A',
                'Nº Factura' => $asset->invoice->number ?? 'N/A',
                'Data Factura' => $asset->invoice && $asset->invoice->date ? 
                    \Carbon\Carbon::parse($asset->invoice->date)->format('d/m/Y') : 'N/A',
                'Código Requisição' => $asset->request->code ?? 'N/A',
                'Tipo Requisição' => $asset->request->type ?? 'N/A',
                'Data Requisição' => $asset->request && $asset->request->date ? 
                    \Carbon\Carbon::parse($asset->request->date)->format('d/m/Y') : 'N/A',
                'Projecto' => $asset->request && $asset->request->project ? 
                    $asset->request->project->name : 'N/A',
                'Código Projecto' => $asset->request && $asset->request->project ? 
                    $asset->request->project->code : 'N/A',
                'Colaborador' => $asset->employee->name ?? 'N/A',
                'Empresa' => $asset->employee && $asset->employee->company ? 
                    $asset->employee->company->name : 'N/A',
                'Data Atribuição' => $asset->assignment_date ? 
                    \Carbon\Carbon::parse($asset->assignment_date)->format('d/m/Y') : 'N/A',
                'Guia Remessa' => $asset->shipment->guide ?? 'N/A',
                'Data Remessa' => $asset->shipment && $asset->shipment->date ? 
                    \Carbon\Carbon::parse($asset->shipment->date)->format('d/m/Y') : 'N/A',
                'Nº Documentos' => $asset->documents ? $asset->documents->count() : 0,
                'Observações' => $asset->notes ?? 'N/A',
                'Data Registo' => $asset->created_at ? 
                    \Carbon\Carbon::parse($asset->created_at)->format('d/m/Y H:i') : 'N/A',
                'Última Actualização' => $asset->updated_at ? 
                    \Carbon\Carbon::parse($asset->updated_at)->format('d/m/Y H:i') : 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $assets->count(),
            'message' => 'Exportação realizada com sucesso'
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro na exportação: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao exportar dados: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
}
    public function quickView(Asset $asset)
    {
        $asset->load(['employee.company', 'supplier', 'invoice', 'request', 'shipment', 'documents']);
        
        return response()->json([
            'success' => true,
            'data' => $asset
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
        public function edit(Asset $asset)
        {
            $asset->load(['employee.company', 'supplier', 'invoice', 'request', 'shipment', 'documents']);
            
            return response()->json([
                'success' => true,
                'data' => $asset
            ]);
        }

    public function store(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:assets,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'total_value' => 'required|numeric|min:0',
            'base_value' => 'required|numeric|min:0',
            'iva_value' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'request_id' => 'nullable|exists:requests,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'warranty_expiry' => 'nullable|date',
        ], [
            'code.unique' => 'Este código já está em uso.',
            'serial_number.unique' => 'Este número de série já está em uso.',
            'total_value.required' => 'O valor total é obrigatório.',
            'base_value.required' => 'O valor base é obrigatório.',
            'iva_value.required' => 'O valor do IVA é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $assetData = $request->only([
                'code', 'name', 'description', 'serial_number', 
                'brand', 'model', 'category', 
                'base_value', 'iva_value', 'total_value',
                'supplier_id', 'invoice_id', 'request_id',
                'shipment_id', 'employee_id',
                'department', 'warranty_expiry', 'purchase_date',
                'location'
            ]);

            // Verificar se os valores são consistentes
            $calculatedTotal = $request->base_value + $request->iva_value;
            if (abs($calculatedTotal - $request->total_value) > 0.01) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'total_value' => ['O valor total deve ser igual à soma do valor base e IVA.']
                    ]
                ], 422);
            }

            // Definir status baseado no employee_id
            $assetData['asset_status'] = $request->employee_id ? 'atribuido' : 'disponivel';
            $assetData['assignment_date'] = $request->employee_id ? now() : null;

            $asset = Asset::create($assetData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activo criado com sucesso!',
                'data' => $asset
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar activo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Asset $asset)
    {
        $asset->load(['employee.company', 'supplier', 'invoice', 'request', 'shipment', 'documents']);
        return response()->json([
            'success' => true,
            'data' => $asset
        ]);
    }

    public function update(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:assets,code,' . $asset->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number,' . $asset->id,
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'total_value' => 'required|numeric|min:0',
            'base_value' => 'required|numeric|min:0',
            'iva_value' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'request_id' => 'nullable|exists:requests,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'warranty_expiry' => 'nullable|date',
        ], [
            'code.unique' => 'Este código já está em uso.',
            'serial_number.unique' => 'Este número de série já está em uso.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $assetData = $request->only([
                'code', 'name', 'description', 'serial_number', 
                'brand', 'model', 'category', 
                'base_value', 'iva_value', 'total_value',
                'supplier_id', 'invoice_id', 'request_id',
                'shipment_id', 'employee_id',
                'department', 'warranty_expiry', 'purchase_date',
                'location'
            ]);

            // Verificar se os valores são consistentes
            $calculatedTotal = $request->base_value + $request->iva_value;
            if (abs($calculatedTotal - $request->total_value) > 0.01) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'total_value' => ['O valor total deve ser igual à soma do valor base e IVA.']
                    ]
                ], 422);
            }

            // Atualizar status baseado no employee_id
            $oldEmployeeId = $asset->employee_id;
            $newEmployeeId = $request->employee_id;
            
            if (!$oldEmployeeId && $newEmployeeId) {
                $assetData['asset_status'] = 'atribuido';
                $assetData['assignment_date'] = now();
            } elseif ($oldEmployeeId && !$newEmployeeId) {
                $assetData['asset_status'] = 'disponivel';
                $assetData['assignment_date'] = null;
            } elseif ($oldEmployeeId && $newEmployeeId && $oldEmployeeId != $newEmployeeId) {
                $assetData['assignment_date'] = now();
            }

            $asset->update($assetData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activo atualizado com sucesso!',
                'data' => $asset
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar activo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Asset $asset)
    {
        try {
            $asset->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Activo eliminado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar activo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assign(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $asset->update([
                'employee_id' => $request->employee_id,
                'assignment_date' => now(),
                'asset_status' => 'atribuido'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activo atribuído com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atribuir activo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeAssignment(Asset $asset)
    {
        try {
            DB::beginTransaction();

            $asset->update([
                'employee_id' => null,
                'assignment_date' => null,
                'asset_status' => 'disponivel'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Atribuição removida com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover atribuição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markInoperational(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $asset->update([
                'asset_status' => 'inoperacional',
                'inoperational_reason' => $request->reason,
                'inoperational_date' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activo marcado como inoperacional!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function writeOff(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $asset->update([
                'asset_status' => 'abatido',
                'write_off_reason' => $request->reason,
                'write_off_date' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activo abatido com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProcessStatus(HttpRequest $request, Asset $asset)
    {
        try {
            $asset->update([
                'process_status' => $request->process_status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status do processo atualizado!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkAction(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:assign,remove_assignment,inoperational,writeOff,delete',
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $assets = Asset::whereIn('id', $request->asset_ids)->get();
            $results = [];
            $successCount = 0;

            foreach ($assets as $asset) {
                try {
                    switch ($request->action) {
                        case 'assign':
                            if ($request->has('employee_id') && $asset->asset_status === 'disponivel') {
                                $asset->update([
                                    'employee_id' => $request->employee_id,
                                    'assignment_date' => now(),
                                    'asset_status' => 'atribuido'
                                ]);
                                $results[$asset->id] = 'Atribuído com sucesso';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não disponível para atribuição';
                            }
                            break;

                        case 'remove_assignment':
                            if ($asset->asset_status === 'atribuido') {
                                $asset->update([
                                    'employee_id' => null,
                                    'assignment_date' => null,
                                    'asset_status' => 'disponivel'
                                ]);
                                $results[$asset->id] = 'Atribuição removida';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não está atribuído';
                            }
                            break;

                        case 'inoperational':
                            $asset->update([
                                'asset_status' => 'inoperacional',
                                'inoperational_date' => now()
                            ]);
                            $results[$asset->id] = 'Marcado como inoperacional';
                            $successCount++;
                            break;

                        case 'writeOff':
                            $asset->update([
                                'asset_status' => 'abatido',
                                'write_off_date' => now()
                            ]);
                            $results[$asset->id] = 'Abatido com sucesso';
                            $successCount++;
                            break;

                        case 'delete':
                            $asset->delete();
                            $results[$asset->id] = 'Eliminado';
                            $successCount++;
                            break;
                    }
                } catch (\Exception $e) {
                    $results[$asset->id] = 'Erro: ' . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => $successCount > 0,
                'message' => "Operação realizada em {$successCount} activo(s)",
                'results' => $results,
                'success_count' => $successCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadDocuments(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_type' => 'nullable|in:manual,garantia,fatura,comprovativo,certificado,outro'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedFiles = [];
            $documentType = $request->document_type ?: 'outro';

            foreach ($request->file('documents') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::random(10) . '.' . $extension;
                $path = $file->storeAs('assets/documents', $filename, 'public');

                $document = AssetDocument::create([
                    'asset_id' => $asset->id,
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                    'document_type' => $documentType,
                ]);

                $uploadedFiles[] = $document;
            }

            return response()->json([
                'success' => true,
                'message' => 'Documentos carregados com sucesso!',
                'data' => $uploadedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadDocument(AssetDocument $document)
    {
        if (!Storage::disk('public')->exists($document->path)) {
            abort(404, 'Ficheiro não encontrado.');
        }

        return Storage::disk('public')->download($document->path, $document->original_name);
    }

    public function removeDocument(AssetDocument $document)
    {
        try {
            Storage::disk('public')->delete($document->path);
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento removido com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listDocuments(Asset $asset)
    {
        $documents = $asset->documents()->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    public function restore($id)
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            $asset->restore();

            return response()->json([
                'success' => true,
                'message' => 'Activo restaurado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            
            // Delete documents
            foreach ($asset->documents as $document) {
                Storage::disk('public')->delete($document->path);
                $document->delete();
            }
            
            $asset->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Activo eliminado permanentemente!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Marcar ativo para manutenção
 */
public function markMaintenance(HttpRequest $request, Asset $asset)
{
    $validator = Validator::make($request->all(), [
        'maintenance_type' => 'required|in:preventiva,corretiva,preditiva',
        'maintenance_description' => 'required|string|max:500',
        'estimated_duration' => 'nullable|integer|min:1',
        'maintenance_provider' => 'nullable|string|max:255'
    ], [
        'maintenance_type.required' => 'O tipo de manutenção é obrigatório.',
        'maintenance_description.required' => 'A descrição da manutenção é obrigatória.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        // Salvar histórico de manutenção
        $maintenance = \App\Models\AssetMaintenance::create([
            'asset_id' => $asset->id,
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->maintenance_description,
            'status' => 'agendada',
            'scheduled_date' => now(),
            'estimated_duration' => $request->estimated_duration,
            'maintenance_provider' => $request->maintenance_provider,
            'notes' => $request->notes
        ]);

        // Atualizar status do ativo
        $asset->update([
            'asset_status' => 'manutencao',
            'last_maintenance' => now(),
            'next_maintenance' => $request->estimated_duration ? 
                now()->addDays((int) $request->estimated_duration) : null
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Activo marcado para manutenção!',
            'data' => $maintenance
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Concluir manutenção
 */
public function completeMaintenance(HttpRequest $request, Asset $asset)
{
    $validator = Validator::make($request->all(), [
        'maintenance_id' => 'required|exists:asset_maintenances,id',
        'actual_duration' => 'nullable|integer|min:1',
        'cost' => 'nullable|numeric|min:0',
        'technician_name' => 'nullable|string|max:255',
        'result' => 'required|in:concluida,pendente,cancelada',
        'notes' => 'nullable|string|max:1000'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        // Atualizar registro de manutenção
        $maintenance = \App\Models\AssetMaintenance::find($request->maintenance_id);
        $maintenance->update([
            'status' => 'concluida',
            'completed_date' => now(),
            'actual_duration' => $request->actual_duration,
            'cost' => $request->cost,
            'technician_name' => $request->technician_name,
            'result' => $request->result,
            'notes' => $request->notes
        ]);

        // Determinar novo status do ativo
        $newStatus = 'disponivel'; // Padrão volta para disponível
        if ($asset->employee_id) {
            $newStatus = 'atribuido'; // Se tinha colaborador, volta para atribuído
        }
        
        if ($request->result === 'pendente') {
            $newStatus = 'inoperacional'; // Se manutenção pendente, fica inoperacional
        }

        // Atualizar ativo
        $asset->update([
            'asset_status' => $newStatus,
            'last_maintenance' => now()
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Manutenção concluída!',
            'data' => [
                'asset' => $asset,
                'maintenance' => $maintenance
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Listar manutenções do ativo
 */
public function listMaintenances(Asset $asset)
{
    $maintenances = $asset->maintenances()
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $maintenances
    ]);
}
}