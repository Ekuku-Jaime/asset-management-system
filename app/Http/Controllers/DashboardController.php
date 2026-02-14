<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Request as AssetRequest;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request as HttpRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Exibir dashboard principal
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * Obter estatísticas gerais com foco em Projects
     */
    public function getStatistics(HttpRequest $request)
    {
        try {
            $period = $this->getPeriodFilter($request);
            $startDate = $period['start'];
            $endDate = $period['end'];

            // Cache das consultas principais (5 minutos)
            $cacheKey = 'dashboard_stats_' . $period['key'] . '_' . now()->format('YmdH');
            
            $data = Cache::remember($cacheKey, 300, function() use ($startDate, $endDate, $period) {
                
                // ===== ESTATÍSTICAS DE PROJETOS =====
                $projectsStats = [
                    'total' => Project::count(),
                    'ativos' => Project::where('status', 'ativo')->count(),
                    'concluidos' => Project::where('status', 'concluido')->count(),
                    'suspensos' => Project::where('status', 'suspenso')->count(),
                    'valor_total' => Project::sum('total_value') ?? 0,
                    'valor_periodo' => Project::whereBetween('created_at', [$startDate, $endDate])->sum('total_value') ?? 0,
                ];

                // Top 5 projetos por valor de ativos
                $topProjectsByValue = Project::select(
                        'projects.id',
                        'projects.name',
                        'projects.code',
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as total_assets_value'),
                        DB::raw('COUNT(DISTINCT assets.id) as assets_count'),
                        DB::raw('COUNT(DISTINCT requests.id) as requests_count')
                    )
                    ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                    ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                    ->groupBy('projects.id', 'projects.name', 'projects.code')
                    ->orderBy('total_assets_value', 'desc')
                    ->limit(5)
                    ->get();

                // Projetos com maior número de ativos
                $topProjectsByAssets = Project::select(
                        'projects.id',
                        'projects.name',
                        'projects.code',
                        DB::raw('COUNT(DISTINCT assets.id) as assets_count'),
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as total_value')
                    )
                    ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                    ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                    ->groupBy('projects.id', 'projects.name', 'projects.code')
                    ->orderBy('assets_count', 'desc')
                    ->limit(5)
                    ->get();

                // Evolução mensal de projetos
                $monthlyProjects = Project::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(total_value) as total_value')
                    )
                    ->whereBetween('created_at', [now()->subMonths(11), now()])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                // ===== ESTATÍSTICAS DE ATIVOS =====
                $assets = Asset::whereBetween('created_at', [$startDate, $endDate])->get();
                
                $assetsStats = [
                    'total' => Asset::count(),
                    'period_total' => $assets->count(),
                    'disponivel' => Asset::where('asset_status', 'disponivel')->count(),
                    'atribuido' => Asset::where('asset_status', 'atribuido')->count(),
                    'inoperacional' => Asset::where('asset_status', 'inoperacional')->count(),
                    'manutencao' => Asset::where('asset_status', 'manutencao')->count(),
                    'abatido' => Asset::where('asset_status', 'abatido')->count(),
                    'valor_total' => Asset::sum('total_value'),
                    'valor_periodo' => $assets->sum('total_value'),
                    'process_completo' => Asset::where('process_status', 'completo')->count(),
                    'process_incompleto' => Asset::where('process_status', 'incompleto')->count(),
                ];

                // ===== TOP COLABORADORES =====
                $topEmployees = Employee::select(
                        'employees.id',
                        'employees.name',
                        'employees.document',
                        'companies.name as company_name',
                        DB::raw('COUNT(DISTINCT assets.id) as assets_count'),
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as assets_total_value')
                    )
                    ->join('companies', 'employees.company_id', '=', 'companies.id')
                    ->leftJoin('assets', 'employees.id', '=', 'assets.employee_id')
                    ->whereNotNull('assets.employee_id')
                    ->groupBy('employees.id', 'employees.name', 'employees.document', 'companies.name')
                    ->orderBy('assets_count', 'desc')
                    ->limit(10)
                    ->get();

                // ===== EMPRESAS COM MAIS ATIVOS =====
                $topCompanies = Company::select(
                        'companies.id',
                        'companies.name',
                        'companies.province',
                        DB::raw('COUNT(DISTINCT assets.id) as assets_count'),
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as assets_total_value'),
                        DB::raw('COUNT(DISTINCT employees.id) as employees_count')
                    )
                    ->leftJoin('employees', 'companies.id', '=', 'employees.company_id')
                    ->leftJoin('assets', 'employees.id', '=', 'assets.employee_id')
                    ->groupBy('companies.id', 'companies.name', 'companies.province')
                    ->orderBy('assets_count', 'desc')
                    ->limit(10)
                    ->get();

                // ===== ATIVOS POR STATUS POR PROJETO =====
                $assetsByProjectAndStatus = Project::select(
                        'projects.name',
                        'assets.asset_status',
                        DB::raw('COUNT(*) as total')
                    )
                    ->join('requests', 'projects.id', '=', 'requests.project_id')
                    ->join('assets', 'requests.id', '=', 'assets.request_id')
                    ->groupBy('projects.name', 'assets.asset_status')
                    ->get()
                    ->groupBy('name');

                // ===== CONTAGENS GERAIS =====
                $counts = [
                    'companies' => Company::count(),
                    'employees' => Employee::count(),
                    'suppliers' => Supplier::count(),
                    'projects' => Project::count(),
                    'invoices' => Invoice::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'shipments' => Shipment::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'requests' => AssetRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
                ];

                // ===== ATIVOS RECENTES =====
                $recentAssets = Asset::with(['employee.company', 'supplier', 'request.project'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function($asset) {
                        return [
                            'id' => $asset->id,
                            'code' => $asset->code,
                            'name' => $asset->name,
                            'asset_status' => $asset->asset_status,
                            'total_value' => $asset->total_value,
                            'project_name' => $asset->request->project->name ?? 'N/A',
                            'employee_name' => $asset->employee->name ?? 'Não atribuído',
                            'company_name' => $asset->employee->company->name ?? 'N/A',
                            'created_at' => $asset->created_at->format('d/m/Y')
                        ];
                    });

                // ===== GARANTIAS A EXPIRAR =====
                $expiringWarranty = Asset::whereNotNull('warranty_expiry')
                    ->whereBetween('warranty_expiry', [now(), now()->addDays(30)])
                    ->with(['request.project', 'employee'])
                    ->orderBy('warranty_expiry')
                    ->limit(10)
                    ->get()
                    ->map(function($asset) {
                        return [
                            'id' => $asset->id,
                            'code' => $asset->code,
                            'name' => $asset->name,
                            'warranty_expiry' => $asset->warranty_expiry,
                            'days_left' => now()->diffInDays($asset->warranty_expiry, false),
                            'project_name' => $asset->request->project->name ?? 'N/A',
                            'asset_status' => $asset->asset_status
                        ];
                    });

                // ===== ATIVOS POR CATEGORIA =====
                $categories = Asset::select('category', DB::raw('count(*) as total'))
                    ->groupBy('category')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $labels = [
                            'hardware' => 'Hardware',
                            'software' => 'Software',
                            'equipamento' => 'Equipamento',
                            'mobiliario' => 'Mobiliário',
                            'veiculo' => 'Veículo',
                            'outro' => 'Outro'
                        ];
                        return [$labels[$item->category] ?? $item->category => $item->total];
                    });

                return [
                    'projects_stats' => $projectsStats,
                    'top_projects_by_value' => $topProjectsByValue,
                    'top_projects_by_assets' => $topProjectsByAssets,
                    'monthly_projects' => $monthlyProjects,
                    'assets_stats' => $assetsStats,
                    'top_employees' => $topEmployees,
                    'top_companies' => $topCompanies,
                    'assets_by_project_status' => $assetsByProjectAndStatus,
                    'counts' => $counts,
                    'categories' => $categories,
                    'recent_assets' => $recentAssets,
                    'expiring_warranty' => $expiringWarranty,
                    'period' => [
                        'label' => $period['label'],
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados para gráficos avançados
     */
    public function getChartsData(HttpRequest $request)
    {
        try {
            $period = $this->getPeriodFilter($request);
            $startDate = $period['start'];
            $endDate = $period['end'];

            $cacheKey = 'dashboard_charts_' . $period['key'] . '_' . now()->format('YmdH');
            
            $data = Cache::remember($cacheKey, 300, function() use ($startDate, $endDate) {
                
                // Gráfico 1: Distribuição de projetos por status
                $projectStatusDistribution = Project::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $labels = [
                            'ativo' => 'Ativo',
                            'concluido' => 'Concluído',
                            'suspenso' => 'Suspenso',
                            'cancelado' => 'Cancelado'
                        ];
                        return [$labels[$item->status] ?? $item->status => $item->total];
                    });

                // Gráfico 2: Evolução de ativos por projeto (top 5)
                $assetsEvolutionByProject = Project::select(
                        'projects.name',
                        DB::raw('MONTH(assets.created_at) as month'),
                        DB::raw('YEAR(assets.created_at) as year'),
                        DB::raw('COUNT(assets.id) as total')
                    )
                    ->join('requests', 'projects.id', '=', 'requests.project_id')
                    ->join('assets', 'requests.id', '=', 'assets.request_id')
                    ->whereBetween('assets.created_at', [now()->subMonths(5), now()])
                    ->groupBy('projects.name', 'year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get()
                    ->groupBy('name');

                // Gráfico 3: Valor total de ativos por projeto
                $projectValueDistribution = Project::select(
                        'projects.name',
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as total_value')
                    )
                    ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                    ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                    ->groupBy('projects.name')
                    ->orderBy('total_value', 'desc')
                    ->limit(10)
                    ->get();

                // Gráfico 4: Distribuição de ativos por empresa
                $assetsByCompany = Company::select(
                        'companies.name',
                        DB::raw('COUNT(assets.id) as total_assets'),
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as total_value')
                    )
                    ->leftJoin('employees', 'companies.id', '=', 'employees.company_id')
                    ->leftJoin('assets', 'employees.id', '=', 'assets.employee_id')
                    ->groupBy('companies.name')
                    ->orderBy('total_assets', 'desc')
                    ->limit(10)
                    ->get();

                // Gráfico 5: Status dos ativos por projeto
                $assetsStatusByProject = Project::select(
                        'projects.name',
                        'assets.asset_status',
                        DB::raw('COUNT(*) as count')
                    )
                    ->join('requests', 'projects.id', '=', 'requests.project_id')
                    ->join('assets', 'requests.id', '=', 'assets.request_id')
                    ->groupBy('projects.name', 'assets.asset_status')
                    ->get()
                    ->groupBy('name');

                // Gráfico 6: Top fornecedores por valor e quantidade
                $topSuppliers = Supplier::select(
                        'suppliers.name',
                        DB::raw('COUNT(assets.id) as asset_count'),
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as total_value'),
                        DB::raw('COUNT(DISTINCT requests.project_id) as projects_count')
                    )
                    ->leftJoin('assets', 'suppliers.id', '=', 'assets.supplier_id')
                    ->leftJoin('requests', 'assets.request_id', '=', 'requests.id')
                    ->groupBy('suppliers.id', 'suppliers.name')
                    ->orderBy('total_value', 'desc')
                    ->limit(10)
                    ->get();

                // Gráfico 7: Evolução do valor total por mês
                $monthlyValueEvolution = Asset::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                        DB::raw('SUM(total_value) as total_value'),
                        DB::raw('COUNT(*) as total_assets')
                    )
                    ->whereBetween('created_at', [now()->subMonths(11), now()])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                // Gráfico 8: Distribuição de garantias por projeto
                $warrantyByProject = Project::select(
                        'projects.name',
                        DB::raw('COUNT(CASE WHEN assets.warranty_expiry <= DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 END) as expiring_soon'),
                        DB::raw('COUNT(CASE WHEN assets.warranty_expiry <= NOW() THEN 1 END) as expired'),
                        DB::raw('COUNT(CASE WHEN assets.warranty_expiry > DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 END) as valid')
                    )
                    ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                    ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                    ->whereNotNull('assets.warranty_expiry')
                    ->groupBy('projects.name')
                    ->get();

                return [
                    'project_status_distribution' => $projectStatusDistribution,
                    'assets_evolution_by_project' => $assetsEvolutionByProject,
                    'project_value_distribution' => $projectValueDistribution,
                    'assets_by_company' => $assetsByCompany,
                    'assets_status_by_project' => $assetsStatusByProject,
                    'top_suppliers' => $topSuppliers,
                    'monthly_value_evolution' => $monthlyValueEvolution,
                    'warranty_by_project' => $warrantyByProject,
                    'colors' => [
                        'primary' => '#4361ee',
                        'success' => '#10b981',
                        'warning' => '#f59e0b',
                        'danger' => '#ef4444',
                        'info' => '#3b82f6',
                        'secondary' => '#8b5cf6',
                        'purple' => '#8b5cf6',
                        'pink' => '#ec4899',
                        'indigo' => '#6366f1',
                        'cyan' => '#06b6d4'
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados dos gráficos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter resumo financeiro detalhado por projeto
     */
    public function getFinancialSummary(HttpRequest $request)
    {
        try {
            $period = $this->getPeriodFilter($request);
            $startDate = $period['start'];
            $endDate = $period['end'];

            $cacheKey = 'dashboard_financial_' . $period['key'] . '_' . now()->format('YmdH');
            
            $data = Cache::remember($cacheKey, 300, function() use ($startDate, $endDate) {
                
                // Resumo financeiro geral
                $generalSummary = Asset::select(
                        DB::raw('COALESCE(SUM(base_value), 0) as total_base'),
                        DB::raw('COALESCE(SUM(iva_value), 0) as total_iva'),
                        DB::raw('COALESCE(SUM(total_value), 0) as total_value'),
                        DB::raw('COUNT(*) as asset_count'),
                        DB::raw('COALESCE(AVG(total_value), 0) as average_value')
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->first();

                // Resumo financeiro por projeto
                $financialByProject = Project::select(
                        'projects.id',
                        'projects.name',
                        'projects.code',
                        'projects.total_value as project_budget',
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as assets_total_value'),
                        DB::raw('COUNT(DISTINCT assets.id) as assets_count'),
                        DB::raw('COALESCE(SUM(invoices.total_value), 0) as invoices_total'),
                        DB::raw('COUNT(DISTINCT invoices.id) as invoices_count')
                    )
                    ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                    ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                    ->leftJoin('invoices', 'assets.invoice_id', '=', 'invoices.id')
                    ->groupBy('projects.id', 'projects.name', 'projects.code', 'projects.total_value')
                    ->orderBy('assets_total_value', 'desc')
                    ->get()
                    ->map(function($project) {
                        $project->budget_usage_percentage = $project->project_budget > 0 
                            ? round(($project->assets_total_value / $project->project_budget) * 100, 2) 
                            : 0;
                        $project->remaining_budget = $project->project_budget - $project->assets_total_value;
                        return $project;
                    });

                // Resumo financeiro por empresa
                $financialByCompany = Company::select(
                        'companies.id',
                        'companies.name',
                        DB::raw('COALESCE(SUM(assets.total_value), 0) as assets_total_value'),
                        DB::raw('COUNT(DISTINCT assets.id) as assets_count'),
                        DB::raw('COUNT(DISTINCT employees.id) as employees_count')
                    )
                    ->leftJoin('employees', 'companies.id', '=', 'employees.company_id')
                    ->leftJoin('assets', 'employees.id', '=', 'assets.employee_id')
                    ->groupBy('companies.id', 'companies.name')
                    ->orderBy('assets_total_value', 'desc')
                    ->limit(10)
                    ->get();

                // Resumo financeiro por categoria
                $financialByCategory = Asset::select(
                        'category',
                        DB::raw('COALESCE(SUM(total_value), 0) as total_value'),
                        DB::raw('COUNT(*) as total_assets'),
                        DB::raw('COALESCE(AVG(total_value), 0) as average_value')
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('category')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $labels = [
                            'hardware' => 'Hardware',
                            'software' => 'Software',
                            'equipamento' => 'Equipamento',
                            'mobiliario' => 'Mobiliário',
                            'veiculo' => 'Veículo',
                            'outro' => 'Outro'
                        ];
                        return [$labels[$item->category] ?? $item->category => [
                            'value' => $item->total_value,
                            'count' => $item->total_assets,
                            'average' => $item->average_value
                        ]];
                    });

                // Evolução financeira mensal
                $monthlyFinancialEvolution = Asset::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                        DB::raw('COALESCE(SUM(total_value), 0) as total_value'),
                        DB::raw('COUNT(*) as total_assets'),
                        DB::raw('COALESCE(AVG(total_value), 0) as average_value')
                    )
                    ->whereBetween('created_at', [now()->subMonths(11), now()])
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                return [
                    'general' => $generalSummary,
                    'by_project' => $financialByProject,
                    'by_company' => $financialByCompany,
                    'by_category' => $financialByCategory,
                    'monthly_evolution' => $monthlyFinancialEvolution,
                    'currency' => 'MT',
                    'period' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter resumo financeiro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter alertas inteligentes
     */
    public function getAlerts()
    {
        try {
            $alerts = [];

            // ===== ALERTAS DE PROJETOS =====
            
            // Projetos sem ativos
            $projectsWithoutAssets = Project::whereDoesntHave('requests.assets')->count();
            if ($projectsWithoutAssets > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'Projetos sem Ativos',
                    'message' => "{$projectsWithoutAssets} projeto(s) sem nenhum ativo registado",
                    'link' => route('projects.index') . '?filter=no_assets',
                    'priority' => 3
                ];
            }

            // Projetos com orçamento excedido
            $exceededBudgetProjects = Project::select('projects.*')
                ->selectRaw('COALESCE(SUM(assets.total_value), 0) as total_assets_value')
                ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                ->groupBy('projects.id')
                ->havingRaw('total_assets_value > projects.total_value')
                ->count();
            
            if ($exceededBudgetProjects > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'fas fa-chart-line',
                    'title' => 'Orçamentos Excedidos',
                    'message' => "{$exceededBudgetProjects} projeto(s) com orçamento excedido",
                    'link' => route('projects.index') . '?filter=exceeded_budget',
                    'priority' => 1
                ];
            }

            // Projetos próximos do orçamento (80%+)
            $nearBudgetProjects = Project::select('projects.*')
                ->selectRaw('COALESCE(SUM(assets.total_value), 0) as total_assets_value')
                ->leftJoin('requests', 'projects.id', '=', 'requests.project_id')
                ->leftJoin('assets', 'requests.id', '=', 'assets.request_id')
                ->groupBy('projects.id')
                ->havingRaw('total_assets_value >= projects.total_value * 0.8')
                ->havingRaw('total_assets_value < projects.total_value')
                ->count();
            
            if ($nearBudgetProjects > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-exclamation-circle',
                    'title' => 'Orçamento Crítico',
                    'message' => "{$nearBudgetProjects} projeto(s) com mais de 80% do orçamento utilizado",
                    'link' => route('projects.index') . '?filter=near_budget',
                    'priority' => 2
                ];
            }

            // ===== ALERTAS DE ATIVOS =====
            
            // Ativos com processo incompleto
            $incompleteProcess = Asset::where('process_status', 'incompleto')->count();
            if ($incompleteProcess > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'Processos Incompletos',
                    'message' => "{$incompleteProcess} ativos com processo incompleto",
                    'link' => route('assets.index') . '?filter=process_incomplete',
                    'priority' => 2
                ];
            }

            // Garantias prestes a expirar (30 dias)
            $expiringWarranty = Asset::whereNotNull('warranty_expiry')
                ->whereBetween('warranty_expiry', [now(), now()->addDays(30)])
                ->count();
            
            if ($expiringWarranty > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-calendar-alt',
                    'title' => 'Garantias a Expirar',
                    'message' => "{$expiringWarranty} ativos com garantia a expirar em 30 dias",
                    'link' => route('assets.index') . '?filter=warranty_expiring',
                    'priority' => 3
                ];
            }

            // Garantias expiradas
            $expiredWarranty = Asset::whereNotNull('warranty_expiry')
                ->where('warranty_expiry', '<', now())
                ->count();
            
            if ($expiredWarranty > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'fas fa-calendar-times',
                    'title' => 'Garantias Expiradas',
                    'message' => "{$expiredWarranty} ativos com garantia expirada",
                    'link' => route('assets.index') . '?filter=warranty_expired',
                    'priority' => 2
                ];
            }

            // Ativos inoperacionais
            $inoperational = Asset::where('asset_status', 'inoperacional')->count();
            if ($inoperational > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'fas fa-exclamation-circle',
                    'title' => 'Ativos Inoperacionais',
                    'message' => "{$inoperational} ativos marcados como inoperacionais",
                    'link' => route('assets.index') . '?filter=inoperacional',
                    'priority' => 1
                ];
            }

            // Ativos em manutenção por mais de 15 dias
            $longMaintenance = Asset::where('asset_status', 'manutencao')
                ->where('updated_at', '<', now()->subDays(15))
                ->count();
            
            if ($longMaintenance > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-tools',
                    'title' => 'Manutenção Prolongada',
                    'message' => "{$longMaintenance} ativos em manutenção há mais de 15 dias",
                    'link' => route('assets.index') . '?filter=long_maintenance',
                    'priority' => 2
                ];
            }

            // Ativos sem manutenção programada
            $noMaintenanceScheduled = Asset::whereNull('next_maintenance')
                ->where('asset_status', '!=', 'abatido')
                ->where('asset_status', '!=', 'inoperacional')
                ->count();
            
            if ($noMaintenanceScheduled > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-calendar',
                    'title' => 'Sem Manutenção Programada',
                    'message' => "{$noMaintenanceScheduled} ativos sem data de manutenção agendada",
                    'link' => route('assets.index') . '?filter=no_maintenance',
                    'priority' => 3
                ];
            }

            // ===== ALERTAS DE EMPRESAS E COLABORADORES =====
            
            // Colaboradores sem ativos atribuídos
            $employeesWithoutAssets = Employee::whereDoesntHave('assets')->count();
            if ($employeesWithoutAssets > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-user',
                    'title' => 'Colaboradores sem Ativos',
                    'message' => "{$employeesWithoutAssets} colaborador(es) sem ativos atribuídos",
                    'link' => route('employees.index') . '?filter=no_assets',
                    'priority' => 4
                ];
            }

            // Empresas sem ativos
            $companiesWithoutAssets = Company::whereDoesntHave('employees.assets')->count();
            if ($companiesWithoutAssets > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-building',
                    'title' => 'Empresas sem Ativos',
                    'message' => "{$companiesWithoutAssets} empresa(s) sem ativos registados",
                    'link' => route('companies.index') . '?filter=no_assets',
                    'priority' => 4
                ];
            }

            // Ordenar alertas por prioridade
            usort($alerts, function($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });

            // Remover campo de prioridade
            $alerts = array_map(function($alert) {
                unset($alert['priority']);
                return $alert;
            }, $alerts);

            return response()->json([
                'success' => true,
                'data' => [
                    'alerts' => $alerts,
                    'total_alerts' => count($alerts),
                    'critical_count' => count(array_filter($alerts, fn($a) => $a['type'] === 'danger')),
                    'warning_count' => count(array_filter($alerts, fn($a) => $a['type'] === 'warning')),
                    'info_count' => count(array_filter($alerts, fn($a) => $a['type'] === 'info'))
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter alertas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados para timeline de projetos
     */
    public function getProjectTimeline()
    {
        try {
            $timeline = Project::select(
                    'id',
                    'name',
                    'code',
                    'start_date',
                    'end_date',
                    'status',
                    'total_value'
                )
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->orderBy('start_date')
                ->limit(15)
                ->get()
                ->map(function($project) {
                    $project->progress = $project->getProgressPercentage();
                    $project->days_remaining = $project->end_date ? now()->diffInDays($project->end_date, false) : null;
                    $project->status_color = match($project->status) {
                        'ativo' => 'success',
                        'concluido' => 'primary',
                        'suspenso' => 'warning',
                        'cancelado' => 'danger',
                        default => 'secondary'
                    };
                    return $project;
                });

            return response()->json([
                'success' => true,
                'data' => $timeline
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter timeline: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter KPI summary
     */
    /**
 * Obter KPI summary
 */
public function getKPISummary()
{
    try {
        // Projetos que têm ativos (via requests)
        $projectsWithAssets = Project::has('requests.assets')->count();
        
        // Total de ativos
        $totalAssets = Asset::count();
        
        // Total de projetos
        $totalProjects = Project::count();
        
        // Projetos concluídos (campo status)
        $completedProjects = Project::where('status', 'concluido')->count();
        
        // Total de colaboradores
        $totalEmployees = Employee::count();
        
        // Ativos atribuídos
        $assignedAssets = Asset::whereNotNull('employee_id')->count();
        
        // Projetos ativos
        $activeProjects = Project::where('status', 'ativo')->count();
        
        // Projetos atrasados
        $overdueProjects = Project::where('status', '!=', 'concluido')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->count();
        
        // Valor total do orçamento
        $totalBudget = Project::sum('total_value') ?? 0;
        
        // Valor total gasto
        $totalSpent = Asset::sum('total_value') ?? 0;
        
        $kpis = [
            'active_projects' => [
                'value' => $activeProjects,
                'trend' => $activeProjects > 0 ? '+8%' : '0%',
                'icon' => 'fas fa-project-diagram',
                'color' => 'primary'
            ],
            'assets_per_project' => [
                'value' => $projectsWithAssets > 0 ? round($totalAssets / $projectsWithAssets, 1) : 0,
                'trend' => $projectsWithAssets > 0 ? '+12%' : '0%',
                'icon' => 'fas fa-cubes',
                'color' => 'success'
            ],
            'avg_asset_value' => [
                'value' => round(Asset::avg('total_value') ?? 0, 2),
                'trend' => '+5%',
                'icon' => 'fas fa-coins',
                'color' => 'warning'
            ],
            'project_completion_rate' => [
                'value' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0,
                'trend' => $totalProjects > 0 ? '+8%' : '0%',
                'icon' => 'fas fa-check-circle',
                'color' => 'info'
            ],
            'assets_per_employee' => [
                'value' => $totalEmployees > 0 ? round($assignedAssets / $totalEmployees, 1) : 0,
                'trend' => $totalEmployees > 0 ? '+3%' : '0%',
                'icon' => 'fas fa-users',
                'color' => 'secondary'
            ],
            'total_assets_value' => [
                'value' => $totalSpent,
                'trend' => '+15%',
                'icon' => 'fas fa-money-bill-wave',
                'color' => 'danger'
            ],
            'budget_utilization' => [
                'value' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0,
                'trend' => $totalBudget > 0 ? '+10%' : '0%',
                'icon' => 'fas fa-chart-line',
                'color' => 'primary'
            ],
            'overdue_projects' => [
                'value' => $overdueProjects,
                'trend' => '-5%',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'danger'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $kpis
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro no getKPISummary: ' . $e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao obter KPIs: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Helper para obter período baseado no filtro
     */
    private function getPeriodFilter(HttpRequest $request)
    {
        $filter = $request->get('period', 'month');

        switch ($filter) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                $label = 'Hoje';
                $key = 'today_' . now()->format('Ymd');
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                $label = 'Esta Semana';
                $key = 'week_' . now()->format('YW');
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                $label = 'Este Mês';
                $key = 'month_' . now()->format('Ym');
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                $label = 'Este Ano';
                $key = 'year_' . now()->format('Y');
                break;
            case 'all':
                $startDate = Carbon::create(2000, 1, 1);
                $endDate = now()->endOfDay();
                $label = 'Todo o Período';
                $key = 'all';
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                $label = 'Este Mês';
                $key = 'month_' . now()->format('Ym');
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'label' => $label,
            'key' => $key
        ];
    }
}