<x-layouts::auth :title="__('Confirmar Palavra-passe')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirmar Palavra-passe')"
            :description="__('Esta é uma área segura da aplicação. Por favor, confirme a sua palavra-passe antes de continuar.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Palavra-passe')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Digite a sua palavra-passe')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 font-bold py-2.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all cursor-pointer" data-test="confirm-password-button">
                {{ __('Confirmar') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>