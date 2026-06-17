<x-layouts::auth :title="__('Forgot password')">
    <div class="relative bg-gradient-to-br from-cyan-400 to-blue-600 p-8 shadow-2xl rounded-tr-[80px] rounded-bl-[20px] rounded-br-[20px] rounded-tl-[20px] text-white">
        
        <div class="flex flex-col gap-6">
            <div class="text-center space-y-2">
                <h2 class="text-2xl font-black uppercase tracking-widest text-white italic">Reset Password</h2>
                <p class="text-cyan-100 text-sm italic">{{ __('Enter your email to receive a password reset link') }}</p>
            </div>

            <x-auth-session-status class="text-center text-white bg-white/20 rounded-lg py-2" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
                @csrf

                <flux:input
                    name="email"
                    :label="__('Email address')"
                    type="email"
                    required
                    autofocus
                    icon="envelope"
                    class="!bg-transparent !border-black !text-white placeholder:text-cyan-100/50 rounded-full border-2"
                    placeholder="email@example.com"
                />

                <flux:button type="submit" class="w-full !bg-black !text-blue-400 hover:!bg-zinc-900 rounded-full font-black text-lg py-6 shadow-xl uppercase tracking-tighter transition-transform active:scale-95">
                    SEND LINK
                </flux:button>
            </form>

            <div class="text-xs text-center text-cyan-100 italic">
                <span>{{ __('Return to') }}</span>
                <flux:link :href="route('login')" class="text-white font-bold hover:underline" wire:navigate>{{ __('log in') }}</flux:link>
            </div>
        </div>
    </div>
</x-layouts::auth>