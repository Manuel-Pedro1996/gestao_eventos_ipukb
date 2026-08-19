<x-layouts::auth :title="__('Redefinir palavra-passe')">
    <div class="relative bg-white dark:bg-zinc-950 border border-gray-200/80 dark:border-zinc-800/50 p-8 sm:p-10 shadow-2xl rounded-tr-[60px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] transition-colors duration-200 w-full max-w-md">
        
        <div class="flex flex-col gap-6 mt-2">
            <div class="text-center space-y-1.5">
                <h2 class="text-2xl font-black uppercase tracking-wider text-gray-900 dark:text-zinc-100 font-sans">
                    {{ __('Nova Palavra-passe') }}
                </h2>
                <p class="text-gray-500 dark:text-zinc-400 text-sm">
                    {{ __('Digite a sua nova palavra-passe abaixo') }}
                </p>
            </div>

            <x-auth-session-status class="text-center text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/30 rounded-xl py-2.5 font-medium" :status="session('status')" />

            <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
                @csrf

                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <flux:input
                    name="email"
                    :value="request('email')"
                    :label="__('Email')"
                    type="email"
                    required
                    autocomplete="email"
                    icon="envelope"
                />

                <flux:input
                    name="password"
                    :label="__('Nova Palavra-passe')"
                    type="password"
                    required
                    autocomplete="new-password"
                    icon="lock-closed"
                    placeholder="••••••••••••"
                    viewable
                />

                <flux:input
                    name="password_confirmation"
                    :label="__('Confirmar Palavra-passe')"
                    type="password"
                    required
                    autocomplete="new-password"
                    icon="check-badge"
                    placeholder="••••••••••••"
                    viewable
                />

                <div class="pt-2">
                    <button type="submit" 
                        style="background-color: #2563eb !important; color: #ffffff !important;"
                        class="w-full font-bold text-sm py-3 rounded-xl shadow-lg hover:opacity-90 uppercase tracking-wider transition-all active:scale-[0.98] cursor-pointer">
                        {{ __('Redefinir Palavra-passe') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::auth>