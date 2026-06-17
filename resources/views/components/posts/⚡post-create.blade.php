<?php

use Livewire\Component;
use App\Models\Post;


new class extends Component
{
    public $title;
    public $content;

   public function rules(): array
   {
        return [
            'title' => ['required','string','max:255'],
            'content' => ['required','string','max:500'],
            
   ];
   }

   public function createPost()
   {
        $this->validate();

        Post::create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => auth()->id(),
            
        ]);

       // session()->flash('message', 'Usuário criado com sucesso!');
        $this->reset(['title', 'content']);
        return redirect()->route('posts.index');
   }

};
?>

<div>
    <flux:heading size="xl">Novo Post</flux:heading>
    <flux:subheading size="lg">Cria um novo post</flux:subheading>
    <flux:separator class="my-4" /> 
    <section class="w-full md:w-1/2 lg:w-1/3">
        <form wire:submit="createPost" class="flex flex-col gap-6">
            <!--título-->
            <flux:input name="title" wire:model="title" :label="__('Título')" type="text" autocomplete="title" placeholder="Digite o título" required />

                <!--conteúdo-->
                <flux:textarea name="content" wire:model="content" :label="__('Conteúdo')"   placeholder="Digite o conteúdo"  />
            <!--password-->

            <div>
                @canany(['criar_posts', 'criar_qualquer_posts'])
                <flux:button type="submit" variant="primary" class="w-full cursor-pointer">
                    Criar Post
                </flux:button>
                @endcanany
            </div>

        </form>
        


    </section>
</div>