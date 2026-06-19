<?php

use Livewire\Volt\Component;
use App\Models\Evento;

new class extends Component {
    // Definimos a propriedade pública
    public Evento $evento;

    // Forçamos o Volt a mapear o parâmetro do ID da URL
    public function mount(Evento $evento)
    {
        $this->evento = $evento;
    }
    
    // --- ESTA É A CORREÇÃO: Dizemos ao Volt qual o layout mestre da aplicação ---
    public function rendering($view)
    {
        $view->layout('layouts.app'); // Altera para 'components.layouts.app' se o teu layout estiver nessa pasta
    }
}; ?>

<div class="py-12">