<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Notifications\BoasVindasNotification;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\DisableBrowserCacheMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Força HTTPS e corrige o upload do Livewire se estiver em produção
       if (config('app.env') === 'production') {
            URL::forceScheme('https');
            
            // Executa após todos os Providers estarem registados
            $this->app->booted(function () {
                if (class_exists(Livewire::class)) {
                    Livewire::setUpdateRoute(function ($handle) {
                        return Route::post('/livewire/update', $handle)
                            ->middleware([
                                'web',
                                'Livewire\Mechanisms\HandleRequests\DisableBrowserCacheMiddleware',
                            ]);
                    });
                }
            });
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Event::listen(Registered::class, function (Registered $event) {
            /** @var User $event->user */
            $user = $event->user;
            $user->notify(new BoasVindasNotification());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}