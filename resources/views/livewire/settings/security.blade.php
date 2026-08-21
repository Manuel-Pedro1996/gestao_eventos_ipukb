<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Definições de segurança') }}</flux:heading>

    <x-settings.layout :heading="__('Atualizar palavra-passe')" :subheading="__('Certifique-se de que a sua conta está a usar uma palavra-passe longa e aleatória para se manter segura')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Palavra-passe atual')"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />
            <flux:input
                wire:model="password"
                :label="__('Nova palavra-passe')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirmar nova palavra-passe')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="text-white bg-blue-600 hover:bg-blue-700 font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all cursor-pointer" data-test="update-password-button">
                    {{ __('Guardar') }}
                </flux:button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
                <flux:heading class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Autenticação de dois fatores (2FA)') }}</flux:heading>
                <flux:subheading class="text-sm text-gray-500 dark:text-gray-400">{{ __('Gerir as definições de autenticação de dois fatores da sua conta') }}</flux:subheading>

                <div class="flex flex-col w-full mx-auto mt-6 space-y-6 text-sm" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <flux:text class="text-gray-600 dark:text-gray-300">
                                {{ __('Será solicitado um código de verificação seguro e aleatório durante o início de sessão, que pode obter na aplicação TOTP instalada no seu telemóvel.') }}
                            </flux:text>

                            <div class="flex justify-start">
                                <flux:button
                                    variant="danger"
                                    wire:click="disable"
                                    class="text-white bg-red-600 hover:bg-red-700 font-bold px-4 py-2 rounded-xl shadow-md transition-all cursor-pointer"
                                >
                                    {{ __('Desativar 2FA') }}
                                </flux:button>
                            </div>

                            <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>
                        </div>
                    @else
                        <div class="space-y-4">
                            <flux:text variant="subtle" class="text-gray-600 dark:text-gray-400">
                                {{ __('Quando ativar a autenticação de dois fatores, ser-lhe-á solicitado um PIN seguro durante o início de sessão. Este código pode ser obtido numa aplicação compatível com TOTP no seu telemóvel.') }}
                            </flux:text>

                            <flux:button
                                variant="primary"
                                wire:click="enable"
                                class="text-white bg-blue-600 hover:bg-blue-700 font-bold px-4 py-2 rounded-xl shadow-lg shadow-blue-500/20 transition-all cursor-pointer"
                            >
                                {{ __('Ativar 2FA') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </section>

            <flux:modal
                name="two-factor-setup-modal"
                class="max-w-md md:min-w-md bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-2xl"
                @close="closeModal"
                wire:model="showModal"
            >
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-4">
                        <div class="p-0.5 w-auto rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                            <div class="p-2.5 rounded-full border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-100 dark:bg-gray-700 relative">
                                <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-gray-200 dark:divide-gray-600 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-gray-200 dark:divide-gray-600 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <flux:icon.qr-code class="relative z-20 text-gray-700 dark:text-gray-200"/>
                            </div>
                        </div>

                        <div class="space-y-2 text-center">
                            <flux:heading size="lg" class="text-gray-900 dark:text-white font-bold">{{ $this->modalConfig['title'] }}</flux:heading>
                            <flux:text class="text-xs text-gray-500 dark:text-gray-400">{{ $this->modalConfig['description'] }}</flux:text>
                        </div>
                    </div>

                    @if ($showVerificationStep)
                        <div class="space-y-6">
                            <div
                                class="flex flex-col items-center space-y-3 justify-center"
                                x-data
                                x-init="$nextTick(() => $el.querySelector('input')?.focus())"
                            >
                                <flux:otp
                                    name="code"
                                    wire:model="code"
                                    length="6"
                                    label="Código OTP"
                                    label:sr-only
                                    class="mx-auto"
                                />
                            </div>

                            <div class="flex items-center space-x-3">
                                <flux:button
                                    variant="outline"
                                    class="flex-1 font-semibold text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                                    wire:click="resetVerification"
                                >
                                    {{ __('Voltar') }}
                                </flux:button>

                                <flux:button
                                    variant="primary"
                                    class="flex-1 text-white bg-blue-600 hover:bg-blue-700 font-bold rounded-xl shadow-lg shadow-blue-500/20 transition-all cursor-pointer"
                                    wire:click="confirmTwoFactor"
                                    x-bind:disabled="$wire.code.length < 6"
                                >
                                    {{ __('Confirmar') }}
                                </flux:button>
                            </div>
                        </div>
                    @else
                        @error('setupData')
                            <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}"/>
                        @enderror

                        <div class="flex justify-center">
                            <div class="relative w-64 overflow-hidden border rounded-2xl border-gray-200 dark:border-gray-700 aspect-square">
                                @empty($qrCodeSvg)
                                    <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-700 animate-pulse">
                                        <flux:icon.loading/>
                                    </div>
                                @else
                                <div x-data class="flex items-center justify-center h-full p-4">
                                    <div
                                        class="bg-white p-3 rounded-xl"
                                        :style="($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)) ? 'filter: invert(1) brightness(1.5)' : ''"
                                    >
                                            {!! $qrCodeSvg !!}
                                        </div>
                                    </div>
                                @endempty
                            </div>
                        </div>

                        <div>
                            <flux:button
                                :disabled="$errors->has('setupData')"
                                variant="primary"
                                class="w-full text-white bg-blue-600 hover:bg-blue-700 font-bold py-2.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all cursor-pointer"
                                wire:click="showVerificationIfNecessary"
                            >
                                {{ $this->modalConfig['buttonText'] }}
                            </flux:button>
                        </div>

                        <div class="space-y-4">
                            <div class="relative flex items-center justify-center w-full">
                                <div class="absolute inset-0 w-full h-px top-1/2 bg-gray-200 dark:bg-gray-700"></div>
                                <span class="relative px-2 text-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                    {{ __('ou insira o código manualmente') }}
                                </span>
                            </div>

                            <div
                                class="flex items-center space-x-2"
                                x-data="{
                                    copied: false,
                                    async copy() {
                                        try {
                                            await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                            this.copied = true;
                                            setTimeout(() => this.copied = false, 1500);
                                        } catch (e) {
                                            console.warn('Não foi possível copiar para a área de transferência');
                                        }
                                    }
                                }"
                            >
                                <div class="flex items-stretch w-full border rounded-xl border-gray-300 dark:border-gray-600 overflow-hidden bg-gray-50 dark:bg-gray-700">
                                    @empty($manualSetupKey)
                                        <div class="flex items-center justify-center w-full p-3 bg-gray-100 dark:bg-gray-700">
                                            <flux:icon.loading variant="mini"/>
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $manualSetupKey }}"
                                            class="w-full p-3 bg-transparent outline-none text-xs font-mono text-gray-900 dark:text-gray-100"
                                        />

                                        <button
                                            @click="copy()"
                                            class="px-3 transition-colors border-l cursor-pointer border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600"
                                        >
                                            <flux:icon.document-duplicate x-show="!copied" variant="outline"></flux:icon>
                                            <flux:icon.check
                                                x-show="copied"
                                                variant="solid"
                                                class="text-green-500"
                                            ></flux:icon>
                                        </button>
                                    @endempty
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </flux:modal>
        @endif
    </x-settings.layout>
</section>