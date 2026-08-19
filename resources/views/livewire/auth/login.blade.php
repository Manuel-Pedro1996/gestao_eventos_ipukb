<x-layouts::auth :title="__('Entrar')">
    <div class="relative bg-white dark:bg-zinc-950 border border-gray-300 dark:border-zinc-800 p-8 sm:p-10 shadow-2xl rounded-tr-[60px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] transition-colors duration-200 w-full max-w-md antialiased">
        
        <div class="absolute top-5 left-5">
            <flux:button :href="route('home')" variant="subtle" size="sm" icon="arrow-left" square wire:navigate inset="top left" title="Voltar ao Início" />
        </div>

        <div class="flex flex-col gap-6 mt-4">
            <div class="text-center space-y-1.5">
                <h2 class="text-2xl font-black uppercase tracking-wider text-gray-900 dark:text-white font-sans">
                    {{ __('Sign In') }}
                </h2>
                <p class="text-gray-700 dark:text-zinc-200 text-sm font-medium">
                    {{ __('Entrar com seu email e palavra passe') }}
                </p>
            </div>

            <x-auth-session-status class="text-center text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 rounded-xl py-2.5 font-medium" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf

                <flux:input
                    name="email"
                    :label="__('Email')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    icon="envelope"
                    placeholder="exemplo@gmail.com"
                    class="text-gray-900 dark:text-white font-semibold"
                />
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
                            class="text-gray-900 dark:text-white font-semibold"
                        />
                    </div>
                    
                    @if (Route::has('password.request'))
                        <div class="flex justify-end mt-1 px-1">
                            <a class="text-xs text-blue-700 dark:text-blue-400 hover:underline font-bold transition-colors" href="{{ route('password.request') }}" wire:navigate>
                                {{ __('Esqueceu a palavra passe?') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="flex items-center px-1 py-0.5">
                    <label class="inline-flex items-center cursor-pointer select-none gap-2 text-sm text-gray-800 dark:text-zinc-200 font-semibold">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-gray-400 dark:border-zinc-600 text-blue-600 focus:ring-0 bg-gray-100 dark:bg-zinc-800"
                        />
                        <span>{{ __('Manter sessão iniciada') }}</span>
                    </label>
                </div>

                {{-- Botão Entrar com estilo garantido no PC e Mobile --}}
                <div class="pt-2">
                    <button type="submit" 
                        style="background-color: #2563eb !important; color: #ffffff !important;"
                        class="w-full font-bold text-sm py-3 rounded-xl shadow-lg hover:opacity-90 uppercase tracking-wider transition-all active:scale-[0.98] cursor-pointer">
                        {{ __('Entrar') }}
                    </button>
                </div>
            </form>

            @if (Route::has('register'))
                <div class="text-sm text-center text-gray-800 dark:text-zinc-300 mt-1 font-medium">
                    <span>{{ __('Não tem uma conta?') }}</span>
                    <a href="{{ route('register') }}" class="text-blue-700 dark:text-blue-400 font-bold hover:underline ml-1" wire:navigate>
                        {{ __('Registar aqui') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts::auth>