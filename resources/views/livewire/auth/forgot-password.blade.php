<x-layouts::auth :title="__('Recuperar palavra-passe')">
    <div class="relative bg-white dark:bg-zinc-950 border border-gray-200/80 dark:border-zinc-800/50 p-8 sm:p-10 shadow-2xl rounded-tr-[60px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] transition-colors duration-200 w-full max-w-md">
        
        <div class="absolute top-5 left-5">
            <flux:button :href="route('login')" variant="subtle" size="sm" icon="arrow-left" square wire:navigate inset="top left" title="Voltar ao Login" />
        </div>

        <div class="flex flex-col gap-6 mt-4">
            <div class="text-center space-y-1.5">
                <h2 class="text-2xl font-black uppercase tracking-wider text-gray-900 dark:text-zinc-100 font-sans">
                    {{ __('Reset Password') }}
                </h2>
                <p class="text-gray-500 dark:text-zinc-400 text-sm">
                    {{ __('Informe o seu email para receber o link de recuperação') }}
                </p>
            </div>

            <x-auth-session-status class="text-center text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/30 rounded-xl py-2.5 font-medium" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
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
                />

                <div class="pt-2">
                    <button type="submit" 
                        style="background-color: #2563eb !important; color: #ffffff !important;"
                        class="w-full font-bold text-sm py-3 rounded-xl shadow-lg hover:opacity-90 uppercase tracking-wider transition-all active:scale-[0.98] cursor-pointer">
                        {{ __('Enviar Link') }}
                    </button>
                </div>
            </form>

            <div class="text-sm text-center text-gray-600 dark:text-zinc-400 mt-1">
                <span>{{ __('Voltar para o') }}</span>
                <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline ml-1" wire:navigate>
                    {{ __('Login') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts::auth>