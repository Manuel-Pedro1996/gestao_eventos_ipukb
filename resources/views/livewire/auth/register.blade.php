<x-layouts::auth :title="__('Registar-se')">
    {{-- Container com Gradiente e Borda Estilo Folha --}}
    <div class="relative bg-gradient-to-br from-cyan-400 to-blue-600 p-8 shadow-2xl rounded-tr-[80px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] text-white">
        
        <div class="flex flex-col gap-6">
            <div class="text-center space-y-2">
                <h2 class="text-3xl font-black uppercase tracking-widest text-white italic">Sign Up</h2>
                <p class="text-cyan-100 text-sm italic">{{ __('Informe as tuas informações para criar conta') }}</p>
            </div>

            <x-auth-session-status class="text-center text-white bg-white/20 rounded-lg py-2" :status="session('status')" />

            <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
                @csrf
                
                <flux:input
                    name="name"
                    :label="__('Nome completo')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    icon="user"
                    class="!bg-transparent !border-black !text-white placeholder:text-cyan-100/50 rounded-full border-2"
                    :placeholder="__('nome do documento')"
                />

                <flux:input
                    name="email"
                    :label="__('Email')"
                    :value="old('email')"
                    type="email"
                    required
                    icon="envelope"
                    class="!bg-transparent !border-black !text-white placeholder:text-cyan-100/50 rounded-full border-2"
                    placeholder="email@example.com"
                />

                <div class="grid grid-cols-1 gap-4">
                    <flux:input
                        name="password"
                        :label="__('Palavra passe')"
                        type="password"
                        required
                        icon="lock-closed"
                        class="!bg-transparent !border-black !text-white placeholder:text-cyan-100/50 rounded-full border-2"
                        :placeholder="__('informe a palavra passe')"
                        viewable
                    />

                    <flux:input
                        name="password_confirmation"
                        :label="__('Confirmar palavra passe')"
                        type="password"
                        required
                        icon="check-badge"
                        class="!bg-transparent !border-black !text-white placeholder:text-cyan-100/50 rounded-full border-2"
                        :placeholder="__('Confirmar a palavra passe')"
                        viewable
                    />
                </div>

                <div class="pt-2">
                    <flux:button type="submit" class="w-full !bg-black !text-blue-400 hover:!bg-zinc-900 rounded-full font-black text-xl py-6 shadow-xl uppercase tracking-tighter transition-transform active:scale-95">
                        REGISTER
                    </flux:button>
                </div>
            </form>

            <div class="text-xs text-center text-cyan-100 italic">
                <span>{{ __('Já tenho uma conta') }}</span>
                <flux:link :href="route('login')" class="text-white font-bold hover:underline" wire:navigate>{{ __('Entrar aqui') }}</flux:link>
            </div>
        </div>
    </div>
</x-layouts::auth>