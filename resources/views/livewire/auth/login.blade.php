<x-layouts::auth :title="__('Entrar')">
    {{-- Container com Gradiente e Borda Estilo Folha --}}
    <div class="relative bg-gradient-to-br from-cyan-400 to-blue-600 p-8 shadow-2xl rounded-tr-[80px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] text-white">
        
        <div class="flex flex-col gap-6">
            {{-- Header customizado --}}
            <div class="text-center space-y-2">
                <h2 class="text-3xl font-black uppercase tracking-widest text-black italic">Sign In</h2>
                <p class="text-black text-sm italic">{{ __('Entrar com seu email e palavra passe') }}</p>
            </div>

            <x-auth-session-status class="text-center text-white bg-white/20 rounded-lg py-2" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf

                {{-- CAMPO: EMAIL --}}
                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-sm font-medium text-black px-1">{{ __('Email') }}</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-cyan-100/70">
                            {{-- Ícone Envelope --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"></path></svg>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            placeholder="email@example.com"
                            class="w-full bg-transparent border-2 border-black/30 focus:border-black text-black placeholder:text-cyan-100/50 rounded-full py-2.5 pl-12 pr-4 outline-none transition-colors"
                        />
                    </div>
                    @error('email')
                        <span class="text-xs text-red-200 px-2 mt-0.5">{{ $message }}</span>
                    @enderror
                </div>

                {{-- CAMPO: PALAVRA PASSE --}}
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-sm font-medium text-black px-1">{{ __('Palavra passe') }}</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-cyan-100/70">
                            {{-- Ícone Cadeado --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"></path></svg>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            required 
                            autocomplete="current-password"
                            placeholder="{{ __('informe a palavra passe') }}"
                            class="w-full bg-transparent border-2 border-black/30 focus:border-black text-black placeholder:text-cyan-100/50 rounded-full py-2.5 pl-12 pr-4 outline-none transition-colors"
                        />
                    </div>
                    @error('password')
                        <span class="text-xs text-red-200 px-2 mt-0.5">{{ $message }}</span>
                    @enderror

                    @if (Route::has('password.request'))
                        <div class="flex justify-end mt-1">
                            <a class="text-xs text-black hover:text-white underline italic transition-colors" href="{{ route('password.request') }}" wire:navigate>
                                {{ __('Recuperar a palavra passe?') }}
                            </a>
                        </div>
                    @endif
                </div>

                {{-- LEMBRAR-ME --}}
                <div class="flex items-center justify-between px-1 py-1">
                    <label class="inline-flex items-center cursor-pointer select-none gap-2 text-sm text-black">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-black/30 text-blue-600 focus:ring-0 bg-transparent"
                        />
                        <span>{{ __('Lembrar-me') }}</span>
                    </label>
                </div>

                {{-- BOTÃO ENTRAR --}}
                <div class="pt-1">
                    <button type="submit" class="w-full bg-black text-blue-400 hover:bg-zinc-900 font-black text-xl py-3.5 rounded-full shadow-xl uppercase tracking-tighter transition-all active:scale-[0.98] cursor-pointer">
                        Entrar
                    </button>
                </div>
            </form>

            {{-- LINK DE REGISTO --}}
            @if (Route::has('register'))
                <div class="text-xs text-center text-black italic mt-2">
                    <span>{{ __('Não tem uma conta?') }}</span>
                    <a href="{{ route('register') }}" class="text-black font-bold hover:underline ml-1" wire:navigate>
                        Registar aqui
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts::auth>