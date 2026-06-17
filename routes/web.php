<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;



Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'dashboard')->middleware('can:visualizar_painel')->name('dashboard');

    Volt::route('users', 'users.user-index')->middleware('can:visualizar_users')->name('users.index');
    Volt::route('users/create', 'users.user-create')->middleware('can:criar_users')->name('users.create');
    Volt::route('users/{user}/edit', 'users.user-edit')->middleware('can:editar_users')->name('users.edit'); 

    Volt::route('clientes', 'clientes.cliente-index')->middleware('can:visualizar_clientes')->name('cliente.index');
     Volt::route('clientes/{user}/edit', 'clientes.cliente-edit')->middleware('can:editar_clientes')->name('cliente.edit');
     Volt::route('clientes/create', 'clientes.cliente-create')->middleware('can:criar_clientes')->name('cliente.create');

    Volt::route('novo-usuario', 'novo-usuario.novousuario-index')->name('novousuario.index');

    Volt::route('roles', 'roles.role-index')->middleware('can:visualizar_roles')->name('roles.index');
    Volt::route('roles/create', 'roles.role-create')->middleware('can:criar_roles')->name('roles.create');
    Volt::route('roles/{role}/edit', 'roles.role-edit')->middleware('can:editar_roles')->name('roles.edit');

    Volt::route('permissions', 'permissions.permission-index')->middleware('can:visualizar_permissions')->name('permissions.index');
    Volt::route('permissions/create', 'permissions.permission-create')->middleware('can:criar_permissions')->name('permissions.create');
    Volt::route('permissions/{permission}/edit', 'permissions.permission-edit')->middleware('can:editar_permissions')->name('permissions.edit');

    Volt::route('eventos', 'eventos.evento-index')->name('eventos.index');
    Volt::route('eventos/create', 'eventos.evento-create')->middleware('can:criar_eventos')->name('eventos.create');
    Volt::route('eventos/{evento}/edit', 'eventos.evento-edit')->middleware('can:editar_eventos')->name('eventos.edit');

    Volt::route('inscricaos', 'inscricaos.inscricao-index')->name('inscricaos.index');
    
    Volt::route('inscricaos/create', 'inscricaos.inscricao-create')->name('inscricaos.create');

    Volt::route('inscricaos/{inscricao}/edit', 'inscricaos.inscricao-edit')->middleware('can:editar_inscricaos')->name('inscricaos.edit');
    
    Volt::route('presencas', 'presencas.presenca-index')->middleware('can:visualizar_presencas')->name('presencas.index');
    Volt::route('presencas/create', 'presencas.presenca-create')->middleware('can:criar_presencas')->name('presencas.create');

    Volt::route('presencas/{presenca}/edit', 'presencas.presenca-edit')->middleware('can:editar_presencas')->name('presencas.edit');


    Route::get('/presencas/imprimir/{evento}', function (App\Models\Evento $evento) {
    $presencas = App\Models\Presenca::whereHas('inscricao', function($q) use ($evento) {
        $q->where('evento_id', $evento->id);
    })->with('inscricao.participante')->get();

    return view('presencas.imprimir', compact('evento', 'presencas'));
    })->name('presencas.imprimir')->middleware('auth');

});

require __DIR__.'/settings.php';
