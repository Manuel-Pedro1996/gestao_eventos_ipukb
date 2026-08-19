<x-layouts::auth :title="__('Verificar E-mail')">
    <div class="relative bg-white dark:bg-zinc-950 border border-gray-200/80 dark:border-zinc-800/50 p-8 sm:p-10 shadow-2xl rounded-tr-[60px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] transition-colors duration-200 w-full max-w-md">
        
        <div class="flex flex-col gap-6 mt-2">
            <div class="text-center space-y-1.5">
                <h2 class="text-2xl font-black uppercase tracking-wider text-gray-900 dark:text-zinc-100 font-sans">
                    {{ __('Verificar E-mail') }}
                </h2>
                <p class="text-gray-500 dark:text-zinc-400 text-sm">
                    {{ __('Por favor, verifica o teu endereço de e-mail clicando no link que acabámos de enviar.') }}
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <x-auth-session-status class="text-center text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/30 rounded-xl py-2.5 font-medium" :status="__('Um novo link de verificação foi enviado para o endereço de e-mail fornecido durante o registo.')" />
            @endif

            <div class="flex flex-col items-center gap-3 pt-2">
                <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                    @csrf
                    <button type="submit" 
                        style="background-color: #2563eb !important; color: #ffffff !important;"
                        class="w-full font-bold text-sm py-3 rounded-xl shadow-lg hover:opacity-90 uppercase tracking-wider transition-all active:scale-[0.98] cursor-pointer">
                        {{ __('Reenviar e-mail de verificação') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full font-bold text-sm text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors py-2 cursor-pointer">
                        {{ __('Terminar sessão') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::auth>