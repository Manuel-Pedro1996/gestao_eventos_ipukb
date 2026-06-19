<x-layouts::auth :title="__('Registar-se')">
    {{-- Container Adaptável com Contraste Reforçado (Idêntico ao Login) --}}
    <div class="relative bg-white dark:bg-zinc-950 border border-gray-200/80 dark:border-zinc-800/80 p-8 sm:p-10 shadow-2xl rounded-tr-[60px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] transition-colors duration-200 w-full max-w-md">
       
        {{-- BOTÃO VOLTAR PARA A WELCOME --}}
        <div class="absolute top-5 left-5">
            <flux:button :href="route('home')" variant="subtle" size="sm" icon="arrow-left" square wire:navigate inset="top left" title="Voltar ao Início" />
        </div>
        
        <div class="flex flex-col gap-6 mt-4">
            {{-- Header Suave e Moderno unificado com o Sign In --}}
            <div class="text-center space-y-1.5">
                <h2 class="text-2xl font-black uppercase tracking-wider text-gray-900 dark:text-white font-sans">
                    {{ __('Sign Up') }}
                </h2>
                <p class="text-gray-600 dark:text-zinc-300 text-sm font-medium">
                    {{ __('Informe os seus dados para criar uma conta') }}
                </p>
            </div>

            <x-auth-session-status class="text-center text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/50 dark:border-emerald-800/50 rounded-xl py-2.5 font-medium" :status="session('status')" />

            <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
                @csrf
                
                {{-- CAMPO: NOME --}}
                <flux:input
                    name="name"
                    :label="__('Nome completo')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    icon="user"
                    placeholder="Ex: Manuel Pedro"
                    class="text-gray-900 dark:text-white font-medium placeholder:text-gray-400 dark:placeholder:text-zinc-500"
                />

                {{-- CAMPO: EMAIL --}}
                <flux:input
                    name="email"
                    :label="__('Email')"
                    :value="old('email')"
                    type="email"
                    required
                    icon="envelope"
                    placeholder="informe email verdadeiro"
                    class="text-gray-900 dark:text-white font-medium placeholder:text-gray-400 dark:placeholder:text-zinc-500"
                />

                {{-- COMPONENTES DE PALAVRA PASSE (Ajustado o grid para mobile/desktop) --}}
                <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                    <flux:input
                        name="password"
                        :label="__('Palavra passe')"
                        type="password"
                        required
                        icon="lock-closed"
                        placeholder="••••••••"
                        viewable
                        class="text-gray-900 dark:text-white font-medium placeholder:text-gray-400 dark:placeholder:text-zinc-500"
                    />

                    <flux:input
                        name="password_confirmation"
                        :label="__('Confirmar')"
                        type="password"
                        required
                        icon="check-badge"
                        placeholder="••••••••"
                        viewable
                        class="text-gray-900 dark:text-white font-medium placeholder:text-gray-400 dark:placeholder:text-zinc-500"
                    />
                </div>

                {{-- BOTÃO REGISTER (Sombra e transição idênticas às do login) --}}
                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-500/20 dark:shadow-none uppercase tracking-wider transition-all active:scale-[0.98] cursor-pointer">
                        {{ __('Registar') }}
                    </button>
                </div>
            </form>

            {{-- LINK DE RETORNO AO LOGIN --}}
            <div class="text-sm text-center text-gray-600 dark:text-zinc-400 mt-1 font-medium">
                <span>{{ __('Já tem uma conta?') }}</span>
                <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline ml-1" wire:navigate>
                    {{ __('Entrar aqui') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts::auth>