<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
  <body class="h-screen flex text-gray-900 dark:text-zinc-100 bg-gray-50 dark:bg-[#09090b] relative overflow-hidden antialiased transition-colors duration-200">
        
        {{-- Efeitos de fundo desfocados --}}
        <div class="hidden dark:block absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-red-600/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="hidden dark:block absolute bottom-[-10%] right-[-10%] w-[60vw] h-[60vw] bg-blue-600/15 rounded-full blur-[140px] pointer-events-none"></div>

        {{-- SIDEBAR: Agora travada estritamente na lateral esquerda --}}
        <flux:sidebar sticky stash collapsible class="h-full border-e border-gray-200 bg-white/80 dark:border-zinc-800/40 dark:bg-zinc-950/40 dark:backdrop-blur-xl">
            <flux:sidebar.header class="flex h-16 items-center justify-between gap-2 px-2">
                <div class="flex items-center overflow-hidden min-w-0 flex-1">
                    <x-app-logo :sidebar="true" href="{{ route('eventos.index') }}" wire:navigate class="truncate" />
                </div>
                <flux:sidebar.toggle class="hidden lg:inline-flex shrink-0" icon="chevron-left" inset="right" />
                <flux:sidebar.collapse class="lg:hidden shrink-0" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Gestão de Eventos')" class="grid">
                    @canany(['visualizar_painel'])
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Painel
                    </flux:sidebar.item>
                    @endcanany
                    
                    <flux:sidebar.item icon="calendar" :href="route('eventos.index')" :current="request()->routeIs('eventos.index')" wire:navigate>
                        Eventos
                    </flux:sidebar.item>
                   
                    <flux:sidebar.item icon="user-plus" :href="route('inscricaos.index')" :current="request()->routeIs('inscricaos.index')" wire:navigate>
                        Inscrições
                    </flux:sidebar.item>
                    
                    @canany(['visualizar_presencas'])
                     <flux:sidebar.item icon="identification" :href="route('presencas.index')" :current="request()->routeIs('presencas.index')" wire:navigate>
                        Presenças
                    </flux:sidebar.item>
                    @endcanany
                    
                    @canany(['visualizar_clientes'])
                    <flux:sidebar.item icon="user-plus" :href="route('cliente.index')" :current="request()->routeIs('cliente.index')" wire:navigate>
                        Clientes
                    </flux:sidebar.item>

                    @endcanany
               
                    @canany(['visualizar_users', 'visualizar_qualquer_users'])
                    <flux:sidebar.item icon="user-plus" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>
                        Usuarios
                    </flux:sidebar.item>
                    @endcanany
                    

                    @canany(['visualizar_roles'])
                    <flux:sidebar.item icon="lock-closed" :href="route('roles.index')" :current="request()->routeIs('roles.index')" wire:navigate>
                        Cargos
                    </flux:sidebar.item>
                    @endcanany

                    @canany(['visualizar_permissions'])
                    <flux:sidebar.item icon="key" :href="route('permissions.index')" :current="request()->routeIs('permissions.index')" wire:navigate>
                        Permissões
                    </flux:sidebar.item>
                    @endcanany  
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        {{-- CONTAINER DA DIREITA: Controla o topo fixo mobile e a rolagem independente --}}
        <div class="flex-1 flex flex-col min-w-0 h-full relative">
            
            <flux:header class="lg:hidden shrink-0 w-full border-b border-gray-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-950/40 backdrop-blur-xl">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
                <flux:spacer />
                <flux:dropdown position="top" align="end">
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>
                        <flux:menu.separator />
                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                Definições
                            </flux:menu.item>
                        </flux:menu.radio.group>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                                Sair
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>

            {{-- CONTEÚDO DINÂMICO (SLOT): Só esta caixa é que vai rolar --}}
            <main class="flex-1 overflow-y-auto focus:outline-none w-full">
                {{ $slot }}
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>            
        @endpersist

        @fluxScripts
    </body>
</html>
