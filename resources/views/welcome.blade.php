<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Bem-vindo') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#fdfdfc] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen font-sans antialiased flex flex-col">

    <header class="sticky top-0 z-50 w-full bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-md border-b border-gray-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <x-app-logo-icon class="h-8 w-auto text-blue-600" />
                <span class="font-bold text-lg sm:text-xl tracking-tight bg-gradient-to-r from-blue-600 to-emerald-500 bg-clip-text text-transparent">
                    Tecnologia e Programação
                </span>
            </div>

            <nav class="flex items-center gap-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                @auth
                    @can('visualizar_painel')
                        <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Painel</a>
                    @endcan
                    <a href="{{ route('eventos.index') }}" class="hover:text-blue-600 transition">Eventos</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-blue-600 transition">Entrar</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Criar Conta</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16 flex-grow">
        <livewire:pesquisar-eventos />
    </main>

    <footer class="w-full bg-white dark:bg-[#0a0a0a] border-t border-zinc-100 dark:border-zinc-800 mt-auto transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-2">
                        <x-app-logo-icon class="h-7 w-auto text-blue-600" />
                        <span class="font-bold text-base tracking-tight bg-gradient-to-r from-blue-600 to-emerald-500 bg-clip-text text-transparent">
                            UKB - Eventos TI
                        </span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-sm leading-relaxed">
                        Plataforma oficial da Universidade Katyavala Bwila para gestão e divulgação de conferências, workshops, hackathons e jornadas científicas de tecnologia.
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">Navegação</h4>
                    <ul class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <li><a href="/" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Início</a></li>
                        <li><a href="{{ route('eventos.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Todos os Eventos</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white mb-4">Contacto</h4>
                    <ul class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <li class="truncate">suporte.ti@ukb.ed.ao</li>
                        <li>Benguela, Angola</li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-zinc-100 dark:border-zinc-900 flex flex-col sm:flex-row justify-between items-center gap-4 text-[11px] text-zinc-400">
                <p>&copy; {{ date('Y') }} Universidade Katyavala Bwila. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>