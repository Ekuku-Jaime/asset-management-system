<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDocument;
use App\Models\AssetMaintenance;
use App\Models\AssetAssignment;
use App\Models\Request as RequestModel;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Employee;
use App\Models\Shipment;
use App\Models\Project;
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

    const PROCESS_STATUSES = [
        'completo' => 'Completo',
        'incompleto' => 'Incompleto'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'asset_statuses' => self::ASSET_STATUSES,
            'process_statuses' => self::PROCESS_STATUSES,
            'categories' => self::CATEGORIES,
            'suppliers' => Supplier::all(),
            'invoices' => Invoice::all(),
            'requests' => RequestModel::with('project','supplier','shipment')->get(),
            'shipments' => Shipment::all(),
            'employees' => Employee::with('company')->get(),
            'projects' => Project::all()
        ];

       
        return view('assets.index', $data);
    }

    /**
     * Datatable data
     */
 public function datatable(HttpRequest $request)
{
    try {
        $query = Asset::with([
            'employee.company',
            'request',
            'request.project',
            'request.supplier',
            'request.invoice',
            'request.shipment',
            'documents',
            'maintenances'
        ])->withTrashed($request->has('show_deleted') && $request->show_deleted);

        // Aplicar filtros existentes
        if ($request->filled('asset_status')) {
            $query->where('asset_status', $request->asset_status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('request_id')) {
            $query->where('request_id', $request->request_id);
        }

        if ($request->filled('project_id')) {
            $query->whereHas('request', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filtro temporal pela data de requisição
        if ($request->filled('date_from')) {
            $query->whereHas('request', function($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('request', function($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_to);
            });
        }

        // Filtro de garantia a expirar
        if ($request->filled('warranty_status')) {
            $today = now();
            $thirtyDays = now()->addDays(30);
            
            switch($request->warranty_status) {
                case 'expired':
                    $query->whereNotNull('warranty_expiry')
                          ->where('warranty_expiry', '<', $today);
                    break;
                case 'expiring':
                    $query->whereNotNull('warranty_expiry')
                          ->where('warranty_expiry', '>=', $today)
                          ->where('warranty_expiry', '<=', $thirtyDays);
                    break;
                case 'valid':
                    $query->whereNotNull('warranty_expiry')
                          ->where('warranty_expiry', '>', $thirtyDays);
                    break;
                case 'none':
                    $query->whereNull('warranty_expiry');
                    break;
            }
        }

        // Filtro de vida útil
        if ($request->filled('life_status')) {
            $today = now();
            
            switch($request->life_status) {
                case 'expired':
                    $query->whereNotNull('life_date')
                          ->where('life_date', '<', $today);
                    break;
                case 'expiring':
                    $query->whereNotNull('life_date')
                          ->where('life_date', '>=', $today)
                          ->where('life_date', '<=', now()->addMonths(6));
                    break;
                case 'valid':
                    $query->whereNotNull('life_date')
                          ->where('life_date', '>', now()->addMonths(6));
                    break;
            }
        }

        return DataTables::eloquent($query)
            // Colunas básicas
            ->addColumn('brand', function($asset) {
                return $asset->brand ?? '';
            })
            ->addColumn('model', function($asset) {
                return $asset->model ?? '';
            })
            ->addColumn('serial_number', function($asset) {
                return $asset->serial_number ?? '';
            })
            ->addColumn('description', function($asset) {
                return $asset->description ?? '';
            })
            ->addColumn('employee_name', function($asset) {
                return $asset->employee ? $asset->employee->name : '';
            })
            ->addColumn('employee_department', function($asset) {
                return $asset->employee ? $asset->employee->department : '';
            })
            ->addColumn('company_name', function($asset) {
                return $asset->employee && $asset->employee->company ? $asset->employee->company->name : '';
            })
            ->addColumn('company_province', function($asset) {
                return $asset->employee && $asset->employee->company ? $asset->employee->company->province : '';
            })
            ->addColumn('supplier_name', function($asset) {
                return $asset->request && $asset->request->supplier ? $asset->request->supplier->name : '';
            })
            ->addColumn('invoice_number', function($asset) {
                return $asset->request && $asset->request->invoice ? $asset->request->invoice->number : '';
            })
            ->addColumn('request_code', function($asset) {
                return $asset->request ? $asset->request->code : '';
            })
            ->addColumn('request_type', function($asset) {
                return $asset->request ? $asset->request->type : '';
            })
            ->addColumn('request_date', function($asset) {
                return $asset->request && $asset->request->date ? (new \DateTime($asset->request->date))->format('d/m/Y') : '';
            })
            ->addColumn('request_status', function($asset) {
                return $asset->request ? $asset->request->process_status : '';
            })
            ->addColumn('shipment_tracking', function($asset) {
                return $asset->request && $asset->request->shipment ? $asset->request->shipment->guide : '';
            })
            ->addColumn('shipment_date', function($asset) {
                return $asset->request && $asset->request->shipment && $asset->request->shipment->date ? (new \DateTime($asset->request->shipment->date))->format('d/m/Y') : '';
            })
            ->addColumn('created_at', function($asset) {
                return $asset->created_at ? $asset->created_at->format('d/m/Y H:i') : '';
            })
            ->addColumn('updated_at', function($asset) {
                return $asset->updated_at ? $asset->updated_at->format('d/m/Y H:i') : '';
            })
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
            ->addColumn('economic_classifier', function($asset) {
                return $asset->economic_classifier ?? '<span class="text-muted">-</span>';
            })
            ->addColumn('life_status', function($asset) {
                if (!$asset->life_date) {
                    return '<span class="badge bg-secondary">Não definida</span>';
                }
                
                try {
                    $lifeDate = new \DateTime($asset->life_date);
                    $today = new \DateTime();
                    
                    if ($lifeDate < $today) {
                        return '<span class="badge bg-danger">Expirada</span>';
                    }
                    
                    $interval = $today->diff($lifeDate);
                    $months = ($interval->y * 12) + $interval->m;
                    
                    if ($months <= 6) {
                        return '<span class="badge bg-warning text-dark">' . $months . ' meses restantes</span>';
                    }
                    
                    return '<span class="badge bg-success">' . $lifeDate->format('d/m/Y') . '</span>';
                } catch (\Exception $e) {
                    return '<span class="badge bg-secondary">Inválida</span>';
                }
            })
            ->addColumn('warranty_indicator', function($asset) {
                if (!$asset->warranty_expiry) {
                    return '<span class="badge bg-secondary">N/A</span>';
                }
                
                try {
                    $expiry = new \DateTime($asset->warranty_expiry);
                    $today = new \DateTime();
                    
                    if ($expiry < $today) {
                        return '<span class="badge bg-danger">Expirada</span>';
                    }
                    
                    $interval = $today->diff($expiry);
                    $days = $interval->days;
                    
                    if ($days <= 30) {
                        return '<span class="badge bg-warning text-dark">' . $days . ' dias</span>';
                    }
                    
                    return '<span class="badge bg-success">' . $expiry->format('d/m/Y') . '</span>';
                } catch (\Exception $e) {
                    return '<span class="badge bg-secondary">Inválida</span>';
                }
            })
            ->addColumn('documents_count', function($asset) {
                $count = $asset->documents ? $asset->documents->count() : 0;
                return $count;
            })
            ->addColumn('maintenances_count', function($asset) {
                $count = $asset->maintenances ? $asset->maintenances()->where('status', '!=', 'concluida')->count() : 0;
                if ($count > 0) {
                    return $count;
                }
                return '<span class="badge bg-secondary">0</span>';
            })
            ->addColumn('request_info', function($asset) {
                if ($asset->request) {
                    $status = $asset->request->process_status === 'completo' ? 'success' : 'warning';
                    return '<div>
                            <div><strong>' . e($asset->request->code) . '</strong></div>
                            <small class="text-muted">' . e($asset->request->type ?? 'N/A') . '</small>
                            <span class="badge bg-' . $status . ' ms-1">' . 
                            ($asset->request->process_status ?? 'N/A') . '</span>
                        </div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('project_name', function($asset) {
                if ($asset->request && $asset->request->project) {
                    return e($asset->request->project->name);
                }
                return 'N/A';
            })
            ->addColumn('employee_info', function($asset) {
                if ($asset->employee) {
                    $companyName = $asset->employee->company ? $asset->employee->company->name : 'Sem empresa';
                    return '<div>
                            <div>' . e($asset->employee->name) . '</div>
                            <small class="text-muted">' . e($companyName) . '</small>
                        </div>';
                }
                return '<span class="text-muted">Não atribuído</span>';
            })
            ->addColumn('formatted_values', function($asset) {
                return '<div>
                        <div>Base: ' . number_format($asset->base_value, 2, ',', '.') . ' MT</div>
                        <div>IVA: ' . number_format($asset->iva_value, 2, ',', '.') . ' MT</div>
                        <div><strong>Total: ' . number_format($asset->total_value, 2, ',', '.') . ' MT</strong></div>
                    </div>';
            })
            ->addColumn('actions', function($asset) {
                $buttons = '<div class="action-buttons">';
                
                if (!$asset->trashed()) {
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
                    
                    // Botão para documentos
                    $buttons .= '<button class="btn-action documents" onclick="showDocumentsModal(' . $asset->id . ')" title="Documentos"><i class="fas fa-file"></i></button>';
                    
                    // Botão para histórico
                    $buttons .= '<button class="btn-action history" onclick="showHistoryModal(' . $asset->id . ')" title="Histórico"><i class="fas fa-history"></i></button>';
                } else {
                    if (auth()->user()->isAdmin()) {
                        $buttons .= '<button class="btn-action restore" onclick="restoreAsset(' . $asset->id . ')" title="Restaurar"><i class="fas fa-undo"></i></button>';
                        $buttons .= '<button class="btn-action force-delete" onclick="forceDeleteAsset(' . $asset->id . ')" title="Eliminar permanentemente"><i class="fas fa-trash-alt"></i></button>';
                    }
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            
            // FILTROS PARA PESQUISA - TODAS AS COLUNAS
            ->filterColumn('brand', function($query, $keyword) {
                $query->where('brand', 'like', "%{$keyword}%");
            })
            ->filterColumn('model', function($query, $keyword) {
                $query->where('model', 'like', "%{$keyword}%");
            })
            ->filterColumn('serial_number', function($query, $keyword) {
                $query->where('serial_number', 'like', "%{$keyword}%");
            })
            ->filterColumn('description', function($query, $keyword) {
                $query->where('description', 'like', "%{$keyword}%");
            })
            ->filterColumn('employee_name', function($query, $keyword) {
                $query->whereHas('employee', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('employee_department', function($query, $keyword) {
                $query->whereHas('employee', function($q) use ($keyword) {
                    $q->where('department', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('company_name', function($query, $keyword) {
                $query->whereHas('employee.company', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('company_province', function($query, $keyword) {
                $query->whereHas('employee.company', function($q) use ($keyword) {
                    $q->where('province', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->whereHas('request.supplier', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('invoice_number', function($query, $keyword) {
                $query->whereHas('request.invoice', function($q) use ($keyword) {
                    $q->where('number', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_code', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_type', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('type', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_date', function($query, $keyword) {
                // Tenta converter a data pesquisada para o formato do banco
                $date = \DateTime::createFromFormat('d/m/Y', $keyword);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $query->whereHas('request', function($q) use ($formattedDate) {
                        $q->whereDate('date', $formattedDate);
                    });
                }
            })
            ->filterColumn('request_status', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('process_status', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('shipment_tracking', function($query, $keyword) {
                $query->whereHas('request.shipment', function($q) use ($keyword) {
                    $q->where('guide', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('shipment_date', function($query, $keyword) {
                $date = \DateTime::createFromFormat('d/m/Y', $keyword);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $query->whereHas('request.shipment', function($q) use ($formattedDate) {
                        $q->whereDate('date', $formattedDate);
                    });
                }
            })
            ->filterColumn('created_at', function($query, $keyword) {
                $date = \DateTime::createFromFormat('d/m/Y', $keyword);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $query->whereDate('created_at', $formattedDate);
                }
            })
            ->filterColumn('updated_at', function($query, $keyword) {
                $date = \DateTime::createFromFormat('d/m/Y', $keyword);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $query->whereDate('updated_at', $formattedDate);
                }
            })
            ->filterColumn('category_badge', function($query, $keyword) {
                // Procura nas categorias pelo nome ou valor
                $categories = array_flip(self::CATEGORIES ?? []);
                $categoryValue = null;
                
                foreach ($categories as $name => $value) {
                    if (stripos($name, $keyword) !== false) {
                        $categoryValue = $value;
                        break;
                    }
                }
                
                if ($categoryValue) {
                    $query->where('category', $categoryValue);
                } else {
                    $query->where('category', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('economic_classifier', function($query, $keyword) {
                $query->where('economic_classifier', 'like', "%{$keyword}%");
            })
            ->filterColumn('status_badge', function($query, $keyword) {
                // Procura nos status pelo nome
                $statuses = array_flip(self::ASSET_STATUSES ?? []);
                $statusValue = null;
                
                foreach ($statuses as $name => $value) {
                    if (stripos($name, $keyword) !== false) {
                        $statusValue = $value;
                        break;
                    }
                }
                
                if ($statusValue) {
                    $query->where('asset_status', $statusValue);
                } else {
                    $query->where('asset_status', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('life_date', function($query, $keyword) {
                $date = \DateTime::createFromFormat('d/m/Y', $keyword);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $query->whereDate('life_date', $formattedDate);
                }
            })
            ->filterColumn('warranty_expiry', function($query, $keyword) {
                $date = \DateTime::createFromFormat('d/m/Y', $keyword);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $query->whereDate('warranty_expiry', $formattedDate);
                }
            })
            ->filterColumn('project_name', function($query, $keyword) {
                $query->whereHas('request.project', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('employee_info', function($query, $keyword) {
                $query->whereHas('employee', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })->orWhereHas('employee.company', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_info', function($query, $keyword) {
                $query->whereHas('request', function($q) use ($keyword) {
                    $q->where('code', 'like', "%{$keyword}%")
                      ->orWhere('type', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('base_value', function($query, $keyword) {
                // Converte formato brasileiro para número
                $keyword = str_replace(['.', ','], ['', '.'], $keyword);
                if (is_numeric($keyword)) {
                    $query->where('base_value', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('iva_value', function($query, $keyword) {
                $keyword = str_replace(['.', ','], ['', '.'], $keyword);
                if (is_numeric($keyword)) {
                    $query->where('iva_value', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('total_value', function($query, $keyword) {
                $keyword = str_replace(['.', ','], ['', '.'], $keyword);
                if (is_numeric($keyword)) {
                    $query->where('total_value', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('documents_count', function($query, $keyword) {
                // Filtro para número de documentos
                if (is_numeric($keyword)) {
                    $query->has('documents', '=', $keyword);
                }
            })
            ->filterColumn('maintenances_count', function($query, $keyword) {
                // Filtro para número de manutenções ativas
                if (is_numeric($keyword)) {
                    $query->whereHas('maintenances', function($q) {
                        $q->where('status', '!=', 'concluida');
                    }, '=', $keyword);
                }
            })
            
            // Ordenações
            ->orderColumn('economic_classifier', 'economic_classifier $1')
            ->orderColumn('life_date', 'life_date $1')
            ->orderColumn('base_value', 'base_value $1')
            ->orderColumn('iva_value', 'iva_value $1')
            ->orderColumn('total_value', 'total_value $1')
            ->orderColumn('warranty_expiry', 'warranty_expiry $1')
            ->orderColumn('created_at', 'created_at $1')
            ->orderColumn('brand', 'brand $1')
            ->orderColumn('model', 'model $1')
            ->orderColumn('serial_number', 'serial_number $1')
            ->orderColumn('employee_name', function($query, $order) {
                $query->orderBy(
                    Employee::select('name')
                        ->whereColumn('employees.id', 'assets.employee_id'),
                    $order
                );
            })
            ->orderColumn('company_name', function($query, $order) {
                $query->orderBy(
                    Company::select('name')
                        ->join('employees', 'companies.id', '=', 'employees.company_id')
                        ->whereColumn('employees.id', 'assets.employee_id'),
                    $order
                );
            })
            ->orderColumn('supplier_name', function($query, $order) {
                $query->orderBy(
                    Supplier::select('name')
                        ->join('requests', 'suppliers.id', '=', 'requests.supplier_id')
                        ->whereColumn('requests.id', 'assets.request_id'),
                    $order
                );
            })
            ->orderColumn('request_code', function($query, $order) {
                $query->orderBy(
                    Request::select('code')
                        ->whereColumn('requests.id', 'assets.request_id'),
                    $order
                );
            })
            ->orderColumn('request_date', function($query, $order) {
                $query->orderBy(
                    Request::select('date')
                        ->whereColumn('requests.id', 'assets.request_id'),
                    $order
                );
            })
            
            ->rawColumns([
                'checkbox',
                'status_badge',
                'category_badge',
                'economic_classifier',
                'life_status',
                'warranty_indicator',
                'documents_count',
                'maintenances_count',
                'request_info',
                'employee_info',
                'formatted_values',
                'actions'
            ])
            ->toJson();

    } catch (\Exception $e) {
        \Log::error('Erro no DataTables de Assets: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'draw' => $request->get('draw', 0),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Erro ao carregar dados: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:assets,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'total_value' => 'required|numeric|min:0',
            'base_value' => 'required|numeric|min:0',
            'iva_value' => 'required|numeric|min:0',
            'economic_classifier' => 'nullable|string|max:50',
            'life_date' => 'nullable|date|after:today',
            'warranty_expiry' => 'nullable|date|after:today',
            'request_id' => 'required|exists:requests,id',
            'employee_id' => 'nullable|exists:employees,id',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ], [
            'code.required' => 'O código do ativo é obrigatório.',
            'code.unique' => 'Este código já está em uso.',
            'name.required' => 'O nome do ativo é obrigatório.',
            'category.required' => 'A categoria é obrigatória.',
            'serial_number.unique' => 'Este número de série já está em uso.',
            'total_value.required' => 'O valor total é obrigatório.',
            'base_value.required' => 'O valor base é obrigatório.',
            'iva_value.required' => 'O valor do IVA é obrigatório.',
            'request_id.required' => 'A requisição é obrigatória.',
            'request_id.exists' => 'A requisição selecionada não existe.',
            'life_date.after' => 'A data de fim de vida útil deve ser futura.',
            'warranty_expiry.after' => 'A data de expiração da garantia deve ser futura.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verificar consistência dos valores
            $calculatedTotal = round($request->base_value + $request->iva_value, 2);
            if (abs($calculatedTotal - $request->total_value) > 0.01) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'total_value' => ['O valor total deve ser igual à soma do valor base e IVA.']
                    ]
                ], 422);
            }

            $assetData = [
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'brand' => $request->brand,
                'model' => $request->model,
                'category' => $request->category,
                'economic_classifier' => $request->economic_classifier,
                'life_date' => $request->life_date,
                'total_value' => $request->total_value,
                'base_value' => $request->base_value,
                'iva_value' => $request->iva_value,
                'request_id' => $request->request_id,
                'employee_id' => $request->employee_id,
                'location' => $request->location,
                'department' => $request->department,
                'warranty_expiry' => $request->warranty_expiry,
                'notes' => $request->notes,
                'asset_status' => $request->employee_id ? 'atribuido' : 'disponivel',
                'assignment_date' => $request->employee_id ? now() : null
            ];

            $asset = Asset::create($assetData);

            // Se atribuído, criar registro no histórico
            if ($request->employee_id) {
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'employee_id' => $request->employee_id,
                    'assignment_date' => now(),
                    'status' => 'atribuido'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo criado com sucesso!',
                'data' => $asset->load(['request.project', 'employee.company'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao criar ativo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar ativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        $asset->load([
            'employee.company', 
            'request', 
            'request.project',
            'documents',
            'maintenances' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'assignments' => function($q) {
                $q->with('employee')->orderBy('created_at', 'desc');
            }
        ]);

        // Adicionar informações da requisição
        if ($asset->request) {
            $asset->request_info = [
                'code' => $asset->request->code,
                'type' => $asset->request->type,
                'date' => $asset->request->date,
                'process_status' => $asset->request->process_status,
                'incomplete_reason' => $asset->request->incomplete_reason,
                'project' => $asset->request->project ? $asset->request->project->name : null
            ];
        }

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
        $asset->load([
            'employee.company', 
            'request', 
            'request.project'
        ]);

        return response()->json([
            'success' => true,
            'data' => $asset
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:assets,code,' . $asset->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number,' . $asset->id,
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'total_value' => 'required|numeric|min:0',
            'base_value' => 'required|numeric|min:0',
            'iva_value' => 'required|numeric|min:0',
            'economic_classifier' => 'nullable|string|max:50',
            'life_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'request_id' => 'required|exists:requests,id',
            'employee_id' => 'nullable|exists:employees,id',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ], [
            'code.unique' => 'Este código já está em uso.',
            'serial_number.unique' => 'Este número de série já está em uso.',
            'request_id.required' => 'A requisição é obrigatória.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verificar consistência dos valores
            $calculatedTotal = round($request->base_value + $request->iva_value, 2);
            if (abs($calculatedTotal - $request->total_value) > 0.01) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'total_value' => ['O valor total deve ser igual à soma do valor base e IVA.']
                    ]
                ], 422);
            }

            // Verificar mudança de employee
            $oldEmployeeId = $asset->employee_id;
            $newEmployeeId = $request->employee_id;

            $assetData = [
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'serial_number' => $request->serial_number,
                'brand' => $request->brand,
                'model' => $request->model,
                'category' => $request->category,
                'economic_classifier' => $request->economic_classifier,
                'life_date' => $request->life_date,
                'total_value' => $request->total_value,
                'base_value' => $request->base_value,
                'iva_value' => $request->iva_value,
                'request_id' => $request->request_id,
                'employee_id' => $newEmployeeId,
                'location' => $request->location,
                'department' => $request->department,
                'warranty_expiry' => $request->warranty_expiry,
                'notes' => $request->notes
            ];

            // Atualizar status baseado no employee
            if (!$oldEmployeeId && $newEmployeeId) {
                $assetData['asset_status'] = 'atribuido';
                $assetData['assignment_date'] = now();
                
                // Criar registro de atribuição
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'employee_id' => $newEmployeeId,
                    'assignment_date' => now(),
                    'status' => 'atribuido'
                ]);
            } elseif ($oldEmployeeId && !$newEmployeeId) {
                $assetData['asset_status'] = 'disponivel';
                $assetData['assignment_date'] = null;
                
                // Fechar atribuição anterior
                $lastAssignment = $asset->assignments()->where('status', 'atribuido')->latest()->first();
                if ($lastAssignment) {
                    $lastAssignment->update([
                        'release_date' => now(),
                        'status' => 'liberado'
                    ]);
                }
            } elseif ($oldEmployeeId && $newEmployeeId && $oldEmployeeId != $newEmployeeId) {
                $assetData['assignment_date'] = now();
                
                // Fechar atribuição anterior
                $lastAssignment = $asset->assignments()->where('status', 'atribuido')->latest()->first();
                if ($lastAssignment) {
                    $lastAssignment->update([
                        'release_date' => now(),
                        'status' => 'liberado'
                    ]);
                }
                
                // Nova atribuição
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'employee_id' => $newEmployeeId,
                    'assignment_date' => now(),
                    'status' => 'atribuido'
                ]);
            }

            $asset->update($assetData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo atualizado com sucesso!',
                'data' => $asset->fresh(['request.project', 'employee.company'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao atualizar ativo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar ativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Asset $asset)
    {
        try {
            // Verificar se pode ser eliminado
            if ($asset->asset_status === 'atribuido') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar um ativo atribuído. Remova a atribuição primeiro.'
                ], 422);
            }

            if ($asset->asset_status === 'manutencao') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível eliminar um ativo em manutenção. Conclua ou cancele a manutenção primeiro.'
                ], 422);
            }

            $asset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ativo eliminado com sucesso!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao eliminar ativo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar ativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted asset
     */
    public function restore($id)
    {
        try {
            $asset = Asset::withTrashed()->findOrFail($id);
            $asset->restore();

            return response()->json([
                'success' => true,
                'message' => 'Ativo restaurado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar ativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete asset
     */
    public function forceDelete($id)
    {
        try {
            DB::beginTransaction();

            $asset = Asset::withTrashed()->findOrFail($id);

            // Eliminar documentos associados
            foreach ($asset->documents as $document) {
                Storage::disk('public')->delete($document->path);
                $document->delete();
            }

            // Eliminar manutenções associadas
            $asset->maintenances()->delete();

            // Eliminar atribuições associadas
            $asset->assignments()->delete();

            // Finalmente, eliminar o ativo
            $asset->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo eliminado permanentemente!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar ativo permanentemente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick view asset details
     */
    public function quickView(Asset $asset)
    {
        $asset->load([
            'employee.company', 
            'request', 
            'request.project',
            'documents',
            'maintenances' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $asset
        ]);
    }

    /**
     * Get statistics
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => Asset::count(),
                'disponivel' => Asset::where('asset_status', 'disponivel')->count(),
                'atribuido' => Asset::where('asset_status', 'atribuido')->count(),
                'inoperacional' => Asset::where('asset_status', 'inoperacional')->count(),
                'manutencao' => Asset::where('asset_status', 'manutencao')->count(),
                'abatido' => Asset::where('asset_status', 'abatido')->count(),
                'garantia_expirada' => Asset::whereNotNull('warranty_expiry')
                    ->where('warranty_expiry', '<', now())
                    ->count(),
                'garantia_proxima' => Asset::whereNotNull('warranty_expiry')
                    ->where('warranty_expiry', '>=', now())
                    ->where('warranty_expiry', '<=', now()->addDays(30))
                    ->count(),
                'vida_util_proxima' => Asset::whereNotNull('life_date')
                    ->where('life_date', '>=', now())
                    ->where('life_date', '<=', now()->addMonths(6))
                    ->count(),
                'vida_util_expirada' => Asset::whereNotNull('life_date')
                    ->where('life_date', '<', now())
                    ->count(),
                'total_valor' => Asset::sum('total_value'),
                'media_valor' => Asset::avg('total_value')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export assets data
     */
    public function export(HttpRequest $request)
    {
        try {
            $query = Asset::with([
                'employee.company',
                'request',
                'request.project',
                'documents'
            ]);

            // Aplicar filtros (mesmos do datatable)
            if ($request->filled('asset_status') && $request->asset_status !== 'all') {
                $query->where('asset_status', $request->asset_status);
            }

            if ($request->filled('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            if ($request->filled('request_id') && $request->request_id) {
                $query->where('request_id', $request->request_id);
            }

            if ($request->filled('employee_id') && $request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('economic_classifier', 'like', "%{$search}%");
                });
            }

            $assets = $query->orderBy('id', 'desc')->get();

            $data = $assets->map(function($asset) {
                $warrantyStatus = 'N/A';
                $lifeStatus = 'N/A';

                if ($asset->warranty_expiry) {
                    try {
                        $expiry = new \DateTime($asset->warranty_expiry);
                        $today = new \DateTime();
                        
                        if ($expiry < $today) {
                            $warrantyStatus = 'Expirada em ' . $expiry->format('d/m/Y');
                        } else {
                            $interval = $today->diff($expiry);
                            $warrantyStatus = 'Válida até ' . $expiry->format('d/m/Y') . ' (' . $interval->days . ' dias)';
                        }
                    } catch (\Exception $e) {
                        $warrantyStatus = 'Inválida';
                    }
                }

                if ($asset->life_date) {
                    try {
                        $lifeDate = new \DateTime($asset->life_date);
                        $today = new \DateTime();
                        
                        if ($lifeDate < $today) {
                            $lifeStatus = 'Expirada em ' . $lifeDate->format('d/m/Y');
                        } else {
                            $interval = $today->diff($lifeDate);
                            $months = ($interval->y * 12) + $interval->m;
                            $lifeStatus = 'Válida até ' . $lifeDate->format('d/m/Y') . ' (' . $months . ' meses)';
                        }
                    } catch (\Exception $e) {
                        $lifeStatus = 'Inválida';
                    }
                }

                return [
                    'ID' => $asset->id,
                    'Código' => $asset->code,
                    'Nome' => $asset->name,
                    'Descrição' => $asset->description,
                    'Categoria' => self::CATEGORIES[$asset->category] ?? $asset->category,
                    'Classificador Económico' => $asset->economic_classifier ?? 'N/A',
                    'Nº Série' => $asset->serial_number ?? 'N/A',
                    'Marca' => $asset->brand ?? 'N/A',
                    'Modelo' => $asset->model ?? 'N/A',
                    'Status' => self::ASSET_STATUSES[$asset->asset_status] ?? $asset->asset_status,
                    'Valor Base (MT)' => number_format($asset->base_value, 2, ',', '.'),
                    'IVA (MT)' => number_format($asset->iva_value, 2, ',', '.'),
                    'Valor Total (MT)' => number_format($asset->total_value, 2, ',', '.'),
                    'Garantia' => $warrantyStatus,
                    'Fim Vida Útil' => $lifeStatus,
                    'Requisição' => $asset->request ? $asset->request->code : 'N/A',
                    'Tipo Requisição' => $asset->request ? $asset->request->type : 'N/A',
                    'Status Requisição' => $asset->request ? $asset->request->process_status : 'N/A',
                    'Projeto' => $asset->request && $asset->request->project ? $asset->request->project->name : 'N/A',
                    'Colaborador' => $asset->employee ? $asset->employee->name : 'Não atribuído',
                    'Empresa' => $asset->employee && $asset->employee->company ? $asset->employee->company->name : 'N/A',
                    'Data Atribuição' => $asset->assignment_date ? \Carbon\Carbon::parse($asset->assignment_date)->format('d/m/Y') : 'N/A',
                    'Localização' => $asset->location ?? 'N/A',
                    'Departamento' => $asset->department ?? 'N/A',
                    'Nº Documentos' => $asset->documents->count(),
                    'Observações' => $asset->notes ?? 'N/A',
                    'Data Registo' => $asset->created_at ? $asset->created_at->format('d/m/Y H:i') : 'N/A',
                    'Última Atualização' => $asset->updated_at ? $asset->updated_at->format('d/m/Y H:i') : 'N/A',
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
                'message' => 'Erro ao exportar dados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign asset to employee
     */
    public function assign(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($asset->asset_status !== 'disponivel') {
                throw new \Exception('Este ativo não está disponível para atribuição.');
            }

            $asset->update([
                'employee_id' => $request->employee_id,
                'assignment_date' => now(),
                'asset_status' => 'atribuido'
            ]);

            AssetAssignment::create([
                'asset_id' => $asset->id,
                'employee_id' => $request->employee_id,
                'assignment_date' => now(),
                'status' => 'atribuido'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo atribuído com sucesso!',
                'data' => $asset->fresh(['employee.company'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atribuir ativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove assignment from asset
     */
    public function removeAssignment(Asset $asset)
    {
        try {
            DB::beginTransaction();

            if ($asset->asset_status !== 'atribuido') {
                throw new \Exception('Este ativo não está atribuído.');
            }

            // Fechar atribuição atual
            $currentAssignment = $asset->assignments()->where('status', 'atribuido')->latest()->first();
            if ($currentAssignment) {
                $currentAssignment->update([
                    'release_date' => now(),
                    'status' => 'liberado'
                ]);
            }

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

    /**
     * Mark asset as inoperational
     */
    public function markInoperational(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if (!in_array($asset->asset_status, ['disponivel', 'atribuido', 'manutencao'])) {
                throw new \Exception('Este ativo não pode ser marcado como inoperacional.');
            }

            $asset->update([
                'asset_status' => 'inoperacional'
            ]);

            AssetMaintenance::create([
                'asset_id' => $asset->id,
                'maintenance_type' => 'corretiva',
                'description' => $request->reason,
                'status' => 'agendada',
                'scheduled_date' => now(),
                'notes' => 'Marcado como inoperacional'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo marcado como inoperacional!'
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
     * Write off asset
     */
    public function writeOff(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if (!in_array($asset->asset_status, ['disponivel', 'atribuido', 'manutencao', 'inoperacional'])) {
                throw new \Exception('Este ativo não pode ser abatido.');
            }

            // Se estiver atribuído, remover atribuição primeiro
            if ($asset->asset_status === 'atribuido') {
                $currentAssignment = $asset->assignments()->where('status', 'atribuido')->latest()->first();
                if ($currentAssignment) {
                    $currentAssignment->update([
                        'release_date' => now(),
                        'status' => 'liberado'
                    ]);
                }
            }

            $asset->update([
                'asset_status' => 'abatido',
                'employee_id' => null,
                'assignment_date' => null
            ]);

            AssetMaintenance::create([
                'asset_id' => $asset->id,
                'maintenance_type' => 'corretiva',
                'description' => 'Ativo abatido: ' . $request->reason,
                'status' => 'concluida',
                'completed_date' => now(),
                'result' => 'cancelada',
                'notes' => $request->reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo abatido com sucesso!'
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
     * Mark asset for maintenance
     */
    public function markMaintenance(HttpRequest $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'maintenance_type' => 'required|in:preventiva,corretiva,preditiva',
            'description' => 'required|string|max:500',
            'estimated_duration' => 'nullable|integer|min:1',
            'maintenance_provider' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if (!in_array($asset->asset_status, ['disponivel', 'atribuido', 'inoperacional'])) {
                throw new \Exception('Este ativo não pode ser marcado para manutenção no estado atual.');
            }

            // Se estiver atribuído, remover atribuição temporariamente
            $wasAssigned = $asset->asset_status === 'atribuido';
            
            // Garantir que estimated_duration é int ou null
            $estimatedDuration = $request->filled('estimated_duration') && is_numeric($request->estimated_duration)
                ? (int) $request->estimated_duration
                : null;

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'maintenance_type' => $request->maintenance_type,
                'description' => $request->description,
                'status' => 'agendada',
                'scheduled_date' => now(),
                'estimated_duration' => $estimatedDuration,
                'maintenance_provider' => $request->maintenance_provider
            ]);

            $asset->update([
                'asset_status' => 'manutencao',
                'last_maintenance' => now(),
                'next_maintenance' => $estimatedDuration ? now()->addDays($estimatedDuration) : null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ativo marcado para manutenção!',
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
     * Complete maintenance
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

            $maintenance = AssetMaintenance::find($request->maintenance_id);
            
            if ($maintenance->asset_id !== $asset->id) {
                throw new \Exception('Esta manutenção não pertence a este ativo.');
            }

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
            $newStatus = 'disponivel';
            
            // Se estava atribuído antes da manutenção, podemos verificar no histórico
            $lastAssignment = $asset->assignments()->where('status', 'liberado')->latest()->first();
            if ($lastAssignment && $lastAssignment->release_date && $lastAssignment->release_date->gt(now()->subDays(30))) {
                $newStatus = 'atribuido';
            }

            if ($request->result === 'pendente') {
                $newStatus = 'inoperacional';
            } elseif ($request->result === 'cancelada') {
                $newStatus = $asset->employee_id ? 'atribuido' : 'disponivel';
            }

            $asset->update([
                'asset_status' => $newStatus
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
     * Get asset history (maintenances and assignments)
     */
    public function getHistory(Asset $asset)
    {
        try {
            $asset->load([
                'maintenances' => function($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'assignments' => function($q) {
                    $q->with('employee')->orderBy('created_at', 'desc');
                }
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'maintenances' => $asset->maintenances,
                    'assignments' => $asset->assignments
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar histórico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions on multiple assets
     */
    public function bulkAction(HttpRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:assign,remove_assignment,mark_maintenance,inoperational,write_off,delete',
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
            'employee_id' => 'required_if:action,assign|exists:employees,id'
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
                            if ($asset->asset_status === 'disponivel') {
                                $asset->update([
                                    'employee_id' => $request->employee_id,
                                    'assignment_date' => now(),
                                    'asset_status' => 'atribuido'
                                ]);
                                AssetAssignment::create([
                                    'asset_id' => $asset->id,
                                    'employee_id' => $request->employee_id,
                                    'assignment_date' => now(),
                                    'status' => 'atribuido'
                                ]);
                                $results[$asset->id] = 'Atribuído com sucesso';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não disponível para atribuição (status: ' . $asset->asset_status . ')';
                            }
                            break;

                        case 'remove_assignment':
                            if ($asset->asset_status === 'atribuido') {
                                $currentAssignment = $asset->assignments()->where('status', 'atribuido')->latest()->first();
                                if ($currentAssignment) {
                                    $currentAssignment->update([
                                        'release_date' => now(),
                                        'status' => 'liberado'
                                    ]);
                                }
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

                        case 'mark_maintenance':
                            if (in_array($asset->asset_status, ['disponivel', 'atribuido', 'inoperacional'])) {
                                AssetMaintenance::create([
                                    'asset_id' => $asset->id,
                                    'maintenance_type' => 'preventiva',
                                    'description' => 'Manutenção em massa',
                                    'status' => 'agendada',
                                    'scheduled_date' => now()
                                ]);
                                $asset->update([
                                    'asset_status' => 'manutencao',
                                    'last_maintenance' => now()
                                ]);
                                $results[$asset->id] = 'Marcado para manutenção';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não pode ser marcado para manutenção';
                            }
                            break;

                        case 'inoperational':
                            if (in_array($asset->asset_status, ['disponivel', 'atribuido', 'manutencao'])) {
                                $asset->update(['asset_status' => 'inoperacional']);
                                $results[$asset->id] = 'Marcado como inoperacional';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não pode ser marcado como inoperacional';
                            }
                            break;

                        case 'write_off':
                            if (in_array($asset->asset_status, ['disponivel', 'atribuido', 'manutencao', 'inoperacional'])) {
                                if ($asset->asset_status === 'atribuido') {
                                    $currentAssignment = $asset->assignments()->where('status', 'atribuido')->latest()->first();
                                    if ($currentAssignment) {
                                        $currentAssignment->update([
                                            'release_date' => now(),
                                            'status' => 'liberado'
                                        ]);
                                    }
                                }
                                $asset->update([
                                    'asset_status' => 'abatido',
                                    'employee_id' => null,
                                    'assignment_date' => null
                                ]);
                                $results[$asset->id] = 'Abatido com sucesso';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não pode ser abatido';
                            }
                            break;

                        case 'delete':
                            if ($asset->asset_status !== 'atribuido' && $asset->asset_status !== 'manutencao') {
                                $asset->delete();
                                $results[$asset->id] = 'Eliminado';
                                $successCount++;
                            } else {
                                $results[$asset->id] = 'Não pode ser eliminado (está atribuído ou em manutenção)';
                            }
                            break;
                    }
                } catch (\Exception $e) {
                    $results[$asset->id] = 'Erro: ' . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => $successCount > 0,
                'message' => "Operação realizada em {$successCount} de " . $assets->count() . " ativo(s)",
                'results' => $results,
                'success_count' => $successCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro na operação em massa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate auto code for new asset
     */
    public function generateCode()
    {
        try {
            $lastAsset = Asset::withTrashed()->orderBy('id', 'desc')->first();
            
            if (!$lastAsset) {
                $code = 'AST-0001';
            } else {
                $lastCode = $lastAsset->code;
                $number = intval(substr($lastCode, 4));
                $newNumber = $number + 1;
                $code = 'AST-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            }

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

    /**
     * Get form options for dropdowns
     */
    public function getFormOptions()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'asset_statuses' => self::ASSET_STATUSES,
                    'categories' => self::CATEGORIES,
                    'suppliers' => Supplier::select('id', 'name')->get(),
                    'invoices' => Invoice::select('id', 'number', 'date', 'status')->get(),
                    'requests' => RequestModel::with('project')->select('id', 'code', 'type', 'process_status')->get(),
                    'shipments' => Shipment::select('id', 'guide', 'date', 'status')->get(),
                    'employees' => Employee::with('company')->select('id', 'name', 'company_id')->get(),
                    'projects' => Project::select('id', 'name', 'code')->get()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar opções: ' . $e->getMessage()
            ], 500);
        }
        
    }

    /**
 * Listar documentos do ativo
 */
public function listDocuments(Asset $asset)
{
    try {
        $documents = $asset->documents()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao listar documentos: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'asset_id' => $asset->id
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao carregar documentos: ' . $e->getMessage()
        ], 500);
    }
}
}