<?php

use Livewire\Component;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\User;
use App\Models\Presenca;
use Illuminate\Support\Facades\DB;

new class extends Component {

    public function rendering($view) {
        $view->title('Painel');
    }

    public function with()
    {
        // 1. Ranking de Inscrições (Gráfico de Barras)
        $eventosRanking = Evento::withCount('inscricoes')
            ->orderBy('inscricoes_count', 'desc')
            ->take(5)
            ->get();

        // 2. Inscrições ao longo dos meses (Gráfico de Linha)
        $inscricoesMensais = Inscricao::select(
                DB::raw('MONTH(created_at) as mes'), 
                DB::raw('count(*) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $mesesNomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $linhaLabels = [];
        $linhaData = [];

        foreach ($inscricoesMensais as $reg) {
            $linhaLabels[] = $mesesNomes[$reg->mes - 1] ?? 'Mês ' . $reg->mes;
            $linhaData[] = $reg->total;
        }

        if (empty($linhaLabels)) {
            $linhaLabels = [ $mesesNomes[date('n') - 1] ];
            $linhaData = [0];
        }

        // 3. Proporção de Presenças vs Ausências (Gráfico de Pizza/Donut)
        $totalInscricoesAtivas = Inscricao::count();
        $presencasConfirmadas = Presenca::whereNotNull('data_checkin')->count();
        $ausenciasConstatadas = max(0, $totalInscricoesAtivas - $presencasConfirmadas);

        // 4. Novas Estatísticas: Clientes vs Utilizadores do Sistema
        $totalClientes = User::doesntHave('roles')->count();
        $totalStaff = User::has('roles')->count();
        $totalGeralUsuarios = User::count();

        return [
            'stats' => [
                'eventos_ativos' => Evento::where('data_fim', '>=', now()->startOfDay())->count(),
                'total_inscritos' => $totalInscricoesAtivas,
                'presencas_confirmadas' => $presencasConfirmadas,
                'inscricoes_canceladas' => Inscricao::onlyTrashed()->count(),
                'total_usuarios' => $totalGeralUsuarios,
                'total_clientes' => $totalClientes,
                'total_staff' => $totalStaff,
            ],
            'barLabels' => $eventosRanking->pluck('titulo')->toArray(),
            'barData' => $eventosRanking->pluck('inscricoes_count')->toArray(),
            'lineLabels' => $linhaLabels,
            'lineData' => $linhaData,
            'pieLabels' => ['Presenças Confirmadas', 'Faltas/Púb. Geral'],
            'pieData' => [$presencasConfirmadas, $ausenciasConstatadas],
            // Dados para o novo gráfico de comunidade
            'userDistLabels' => ['Clientes (Participantes)', 'Equipa do Sistema'],
            'userDistData' => [$totalClientes, $totalStaff]
        ];
    }
}; ?>

<div wire:poll.10s class="p-6 w-full space-y-6">
    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    <div>
        <flux:heading size="xl" level="1">Painel Estatístico</flux:heading>
        <flux:subheading>Visão geral do sistema de eventos e inscrições</flux:subheading>
    </div>

    <flux:separator class="my-4" />

    {{-- Grid de Cards Informativos --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card class="flex flex-col items-center justify-center p-6 text-center shadow-sm">
            <flux:icon.calendar-days class="size-8 text-blue-500 mb-2" />
            <flux:heading size="lg">{{ $stats['eventos_ativos'] }}</flux:heading>
            <flux:subheading>Eventos Disponíveis</flux:subheading>
        </flux:card>

        <flux:card class="flex flex-col items-center justify-center p-6 text-center shadow-sm">
            <flux:icon.users class="size-8 text-green-500 mb-2" />
            <flux:heading size="lg">{{ $stats['total_inscritos'] }}</flux:heading>
            <flux:subheading>Total de Inscritos</flux:subheading>
        </flux:card>

        <flux:card class="flex flex-col items-center justify-center p-6 text-center shadow-sm">
            <flux:icon.check-badge class="size-8 text-indigo-500 mb-2" />
            <flux:heading size="lg">{{ $stats['presencas_confirmadas'] }}</flux:heading>
            <flux:subheading>Presenças Confirmadas</flux:subheading>
        </flux:card>

        <flux:card class="flex flex-col items-center justify-center p-6 text-center shadow-sm">
            <flux:icon.x-circle class="size-8 text-red-500 mb-2" />
            <flux:heading size="lg">{{ $stats['inscricoes_canceladas'] }}</flux:heading>
            <flux:subheading>Cancelamentos</flux:subheading>
        </flux:card>
    </div>

    {{-- Grid Principal de Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- 1. Gráfico de Barras --}}
        <flux:card class="lg:col-span-2" wire:ignore>
            <flux:heading size="md" class="mb-4">Inscrições por Evento (Top 5)</flux:heading>
            <div class="h-72 relative">
                <canvas id="rankingChart"></canvas>
            </div>
        </flux:card>

        {{-- 2. Gráfico de Pizza (Taxa de Comparência) --}}
        <flux:card wire:ignore>
            <flux:heading size="md" class="mb-4">Taxa de Comparência</flux:heading>
            <div class="h-72 relative flex items-center justify-center">
                <canvas id="attendancePieChart"></canvas>
            </div>
        </flux:card>

        {{-- 3. Gráfico de Linha (Evolução Mensal) --}}
        <flux:card class="lg:col-span-2" wire:ignore>
            <flux:heading size="md" class="mb-4">Evolução Mensal de Inscrições (Ano Corrente)</flux:heading>
            <div class="h-72 relative">
                <canvas id="monthlyLineChart"></canvas>
            </div>
        </flux:card>

        {{-- 4. NOVO: Distribuição de Utilizadores (Clientes vs Sistema) --}}
        <flux:card wire:ignore>
            <flux:heading size="md" class="mb-2">Estrutura de Contas</flux:heading>
            <div class="h-56 relative flex items-center justify-center">
                <canvas id="userDistributionChart"></canvas>
            </div>
            <div class="mt-4 pt-2 border-t border-zinc-100 dark:border-zinc-800 grid grid-cols-2 text-center text-xs">
                <div>
                    <span class="text-zinc-500 block">Clientes</span>
                    <strong class="text-zinc-900 dark:text-white text-sm">{{ $stats['total_clientes'] }}</strong>
                </div>
                <div class="border-l border-zinc-200 dark:border-zinc-700">
                    <span class="text-zinc-500 block">Utilizadores/Staff</span>
                    <strong class="text-zinc-900 dark:text-white text-sm">{{ $stats['total_staff'] }}</strong>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Contador Geral da Comunidade --}}
    <flux:card class="bg-zinc-50 dark:bg-zinc-900/40 p-4 flex flex-row items-center justify-between">
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            Total global de utilizadores registados na base de dados: 
            <strong class="text-zinc-900 dark:text-white ml-1 text-base">{{ $stats['total_usuarios'] }}</strong>
        </div>
        <div class="text-xs text-zinc-400 dark:text-zinc-500 italic">
            Atualização em tempo real ativa.
        </div>
    </flux:card>

    {{-- SCRIPT DE CONTROLO DO CHART.JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.appCharts = window.appCharts || {};

            function renderAllCharts() {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(63, 63, 70, 0.3)' : 'rgba(228, 228, 231, 0.6)';
                const textColor = isDark ? '#a1a1aa' : '#71717a';

                // --- 1. GRÁFICO DE BARRAS ---
                const ctxBar = document.getElementById('rankingChart');
                if (ctxBar) {
                    if (window.appCharts.bar) window.appCharts.bar.destroy();
                    window.appCharts.bar = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: @js($barLabels),
                            datasets: [{
                                label: 'Inscritos',
                                data: @js($barData),
                                backgroundColor: 'rgba(59, 130, 246, 0.65)',
                                borderColor: 'rgb(59, 130, 246)',
                                borderWidth: 1.5,
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor } },
                                x: { grid: { display: false }, ticks: { color: textColor } }
                            }
                        }
                    });
                }

                // --- 2. GRÁFICO DE PIZZA (DONUT) - PRESENÇAS ---
                const ctxPie = document.getElementById('attendancePieChart');
                if (ctxPie) {
                    if (window.appCharts.pie) window.appCharts.pie.destroy();
                    window.appCharts.pie = new Chart(ctxPie, {
                        type: 'doughnut',
                        data: {
                            labels: @js($pieLabels),
                            datasets: [{
                                data: @js($pieData),
                                backgroundColor: ['rgba(99, 102, 241, 0.75)', 'rgba(244, 63, 94, 0.65)'],
                                borderColor: isDark ? '#18181b' : '#ffffff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12, padding: 15 } }
                            },
                            cutout: '65%'
                        }
                    });
                }

                // --- 3. GRÁFICO DE LINHA - EVOLUÇÃO ---
                const ctxLine = document.getElementById('monthlyLineChart');
                if (ctxLine) {
                    if (window.appCharts.line) window.appCharts.line.destroy();
                    window.appCharts.line = new Chart(ctxLine, {
                        type: 'line',
                        data: {
                            labels: @js($lineLabels),
                            datasets: [{
                                label: 'Inscrições Efetuadas',
                                data: @js($lineData),
                                fill: true,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 3,
                                tension: 0.35,
                                pointBackgroundColor: 'rgb(16, 185, 129)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                                x: { grid: { color: gridColor }, ticks: { color: textColor } }
                            }
                        }
                    });
                }

                // --- 4. NOVO GRÁFICO DE DISTRIBUIÇÃO DE UTILIZADORES ---
                const ctxUserDist = document.getElementById('userDistributionChart');
                if (ctxUserDist) {
                    if (window.appCharts.userDist) window.appCharts.userDist.destroy();
                    window.appCharts.userDist = new Chart(ctxUserDist, {
                        type: 'doughnut',
                        data: {
                            labels: @js($userDistLabels),
                            datasets: [{
                                data: @js($userDistData),
                                backgroundColor: [
                                    'rgba(16, 185, 129, 0.7)',  // Esmeralda para Clientes
                                    'rgba(245, 158, 11, 0.7)'   // Âmbar/Laranja para Staff
                                ],
                                borderColor: isDark ? '#18181b' : '#ffffff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12, padding: 10 } }
                            },
                            cutout: '70%'
                        }
                    });
                }
            }

            renderAllCharts();

            // Hook do Livewire para manter os gráficos intactos e atualizados após cada Poll
            Livewire.hook('request', ({ respond }) => {
                respond(() => {
                    setTimeout(renderAllCharts, 50);
                });
            });
        });
    </script>
</div>