<x-layouts::auth :title="__('Entrar')">
    {{-- Container Adaptável (Modo Claro: Fundo Branco | Modo Escuro: Fundo Zinco Profundo) --}}
    <div class="relative bg-white dark:bg-zinc-950 border border-gray-200/80 dark:border-zinc-800/50 p-8 sm:p-10 shadow-2xl rounded-tr-[60px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] transition-colors duration-200 w-full max-w-md">
        
        {{-- BOTÃO VOLTAR PARA A WELCOME --}}
        <div class="absolute top-5 left-5">
            <flux:button :href="route('home')" variant="subtle" size="sm" icon="arrow-left" square wire:navigate inset="top left" title="Voltar ao Início" />
        </div>

        <div class="flex flex-col gap-6 mt-4">
            {{-- Header Suave e Moderno --}}
            <div class="text-center space-y-1.5">
                <h2 class="text-2xl font-black uppercase tracking-wider text-gray-900 dark:text-zinc-100 font-sans">
                    {{ __('Sign In') }}
                </h2>
                <p class="text-gray-500 dark:text-zinc-400 text-sm">
                    {{ __('Entrar com seu email e palavra passe') }}
                </p>
            </div>

            <x-auth-session-status class="text-center text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/30 rounded-xl py-2.5 font-medium" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf

                {{-- CAMPO: EMAIL --}}
                <flux:input
                    name="email"
                    :label="__('Email')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    icon="envelope"
                    placeholder="exemplo@gmail.com"
                />

                {{-- CAMPO: PALAVRA PASSE --}}
                <div class="flex flex-col gap-1">
                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Palavra passe')"
                            type="password"
                            required
                            autocomplete="current-password"
                            icon="lock-closed"
                            placeholder="••••••••••••"
                            viewable
                        />
                    </div>
                    
                    @if (Route::has('password.request'))
                        <div class="flex justify-end mt-1 px-1">
                            <a class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium transition-colors" href="{{ route('password.request') }}" wire:navigate>
                                {{ __('Esqueceu a palavra passe?') }}
                            </a>
                        </div>
                    @endif
                </div>

                {{-- LEMBRAR-ME --}}
                <div class="flex items-center px-1 py-0.5">
                    <label class="inline-flex items-center cursor-pointer select-none gap-2 text-sm text-gray-600 dark:text-zinc-400 font-medium">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-gray-300 dark:border-zinc-700 text-blue-600 focus:ring-0 bg-transparent dark:bg-zinc-900"
                        />
                        <span>{{ __('Manter sessão iniciada') }}</span>
                    </label>
                </div>

                {{-- BOTÃO ENTRAR --}}
                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm py-3 rounded-xl shadow-lg shadow-blue-500/10 dark:shadow-none uppercase tracking-wider transition-all active:scale-[0.98] cursor-pointer">
                        {{ __('Entrar') }}
                    </button>
                </div>
            </form>

            {{-- LINK DE REGISTO --}}
            @if (Route::has('register'))
                <div class="text-sm text-center text-gray-500 dark:text-zinc-500 mt-1">
                    <span>{{ __('Não tem uma conta?') }}</span>
                    <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline ml-1" wire:navigate>
                        {{ __('Registar aqui') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts::auth>