<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Welcome') }} - {{ config('app.name', '') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .bg-ukb-mesh {
                background-color: #fdfdfc;
                background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%),
                                  radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
            }
            .dark .bg-ukb-mesh {
                background-color: #0a0a0a;
                background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.1) 0px, transparent 50%),
                                  radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
            }
        </style>
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="bg-ukb-mesh text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen font-sans antialiased m-0 p-0"> {{-- Removido p-6 --}}

        @php
            use App\Models\Evento;
            use Illuminate\Support\Facades\Request;

            $search = Request::get('search');
            $dateSearch = Request::get('date');
            $query = Evento::query()->where('data_evento', '>=', now());

            if ($search) { $query->where('titulo', 'like', '%' . $search . '%'); }
            if ($dateSearch) { $query->whereDate('data_evento', $dateSearch); }

            $eventosPublicos = $query->orderByRaw('vagas_disponiveis > 0 DESC')->orderBy('data_evento', 'asc')->get();
        @endphp

        <header class="sticky top-0 z-50 w-full bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-md border-b border-gray-100 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <x-app-logo-icon class="h-8 w-auto" />
                    <span class="font-bold text-lg sm:text-xl tracking-tight bg-gradient-to-r from-blue-600 to-emerald-500 bg-clip-text text-transparent">
                        Tecnologia e Programação
                    </span>
                </div>
                
                <nav class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs sm:text-sm font-medium border border-zinc-200 dark:border-zinc-700 rounded-full hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-1.5 text-xs sm:text-sm font-medium hover:text-blue-600 transition">Entrar</a>
                        <a href="{{ route('register') }}" class="px-4 py-1.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white rounded-full transition transform active:scale-95">Criar Conta</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
            {{-- Cabeçalho e Busca --}}
            <div class="text-center max-w-3xl mx-auto mb-12 lg:mb-16">
                <h1 class="text-3xl sm:text-4xl lg:text-6xl font-black tracking-tight text-zinc-900 dark:text-white mb-6 px-2">
                    Descubra o seu próximo <span class="text-blue-600">evento</span> TI
                </h1>
                
                <form action="/" method="GET" class="p-2 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Pesquisar..." class="flex-1 bg-transparent border-none focus:ring-0 text-sm px-4">
                    <div class="flex items-center gap-2">
                        <input type="date" name="date" value="{{ $dateSearch }}" class="bg-transparent border-none focus:ring-0 text-xs sm:text-sm uppercase">
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm">Pesquisar</button>
                    </div>
                </form>
            </div>

            {{-- Grid de Cartões --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @forelse ($eventosPublicos as $evento)
                    <article class="flex flex-col bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden border border-zinc-100 dark:border-zinc-800 shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="relative h-48 sm:h-56 w-full">
                            <img src="{{ $evento->foto ? asset('storage/' . $evento->foto) : asset('img/sem-foto.jpg') }}" class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute top-3 left-3">
                                <span class="{{ $evento->vagas_disponiveis > 0 ? 'bg-blue-600' : 'bg-red-600' }} text-white text-[10px] font-bold uppercase px-3 py-1 rounded-full shadow-lg">
                                    {{ $evento->vagas_disponiveis > 0 ? $evento->vagas_disponiveis . ' Vagas' : 'Esgotado' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-1">
                            <p class="text-blue-600 dark:text-blue-400 text-[10px] font-bold uppercase tracking-widest mb-2">
                                {{ \Carbon\Carbon::parse($evento->data_evento)->translatedFormat('d M Y') }}
                            </p>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3 line-clamp-1">{{ $evento->titulo }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-6 flex-1">{{ $evento->descricao }}</p>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-zinc-50 dark:border-zinc-800">
                                <span class="text-[11px] text-zinc-400 truncate max-w-[120px]">{{ $evento->local }}</span>
                                <a href="{{ route('eventos.index') }}" class="text-sm font-bold text-blue-600">Ver detalhes →</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-20 text-center border-2 border-dashed border-zinc-100 dark:border-zinc-800 rounded-3xl">
                        <p class="text-zinc-500">Nenhum evento encontrado.</p>
                    </div>
                @endforelse
            </div>
        </main>

        <footer class="w-full bg-white dark:bg-[#0a0a0a] border-t border-zinc-100 dark:border-zinc-800 mt-auto transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    {{-- Coluna 1: Sobre / Logo --}}
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-app-logo-icon class="h-7 w-auto" />
                            <span class="font-bold text-base tracking-tight bg-gradient-to-r from-blue-600 to-emerald-500 bg-clip-text text-transparent">
                                UKB - Eventos TI
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-sm leading-relaxed">
                            Plataforma oficial da Universidade Katyavala Bwila para gestão e divulgação de conferências, workshops, hackathons e jornadas científicas de tecnologia.
                        </p>
                    </div>

                    {{-- Coluna 2: Links Rápidos --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">Navegação</h4>
                        <ul class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <li><a href="/" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Início</a></li>
                            <li><a href="{{ route('eventos.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Todos os Eventos</a></li>
                            <li><a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Sobre a UKB</a></li>
                        </ul>
                    </div>

                    {{-- Coluna 3: Suporte / Contacto --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">Contacto</h4>
                        <ul class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <li class="truncate">suporte.ti@ukb.ed.ao</li>
                            <li>Benguela, Angola</li>
                        </ul>
                    </div>
                </div>

                {{-- Linha de Copyright Inferior --}}
                <div class="pt-8 border-t border-zinc-100 dark:border-zinc-900 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] text-zinc-400">
                    <p>&copy; {{ date('Y') }} Universidade Katyavala Bwila. Todos os direitos reservados.</p>
                    <div class="flex gap-4">
                        <a href="#" class="hover:underline">Políticas de Privacidade</a>
                        <a href="#" class="hover:underline">Termos de Uso</a>
                    </div>
                </div>
            </div>
        </footer>

        {{-- Botão Voltar ao Topo --}}
        <button 
            id="btn-topo" 
            onclick="voltarAoTopo()"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 invisible opacity-0 flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-5 py-3 rounded-full shadow-2xl transition-all duration-300 transform translate-y-4 cursor-pointer"
        >
            <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"></path>
            </svg>
            Voltar ao Topo
        </button>

        <script>
            const btnTopo = document.getElementById('btn-topo');

            // Escuta o evento de scroll da página
            window.onscroll = function() {
                // Se rolar mais de 400 píxeis para baixo, mostra o botão
                if (document.body.scrollTop > 400 || document.documentElement.scrollTop > 400) {
                    btnTopo.classList.remove('invisible', 'opacity-0', 'translate-y-4');
                    btnTopo.classList.add('visible', 'opacity-100', 'translate-y-0');
                } else {
                    btnTopo.classList.remove('visible', 'opacity-100', 'translate-y-0');
                    btnTopo.classList.add('invisible', 'opacity-0', 'translate-y-4');
                }
            };

            // Função que faz o efeito de scroll suave até ao topo
            function voltarAoTopo() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        </script>
   
    </body>
</html>