<?php

use Livewire\Component;
use App\Models\Post;
use Illuminate\Validation\Rule;


new class extends Component
{
    public Post $post;
    public string $title= '';
    public string $content= '';
    
   public function rules(): array
   {
        return [
            'title' => ['required','string','max:255'],
            'content' => ['required','string','max:500'],
   ];
   }

   public function mount(Post $post)
   {
        $this->post = $post;
        $this->title =$post->title;
        $this->content =$post->content;
   }

   public function updatePost()
   {
        $this->validate();

        $this->post->update([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => auth()->id(),
        ]);

        //session()->flash('message', 'Usuário criado com sucesso!');

        $this->reset(['title', 'content']);
        return redirect()->route('posts.index');
   }

};
?>
<div>
    <flux:heading size="xl">Editar Post</flux:heading>
    <flux:subheading size="lg">Editar dados de um Post</flux:subheading>
    <flux:separator class="my-4" /> 
    <section class="w-full md:w-1/2 lg:w-1/3">
        <form wire:submit="updatePost" class="flex flex-col gap-6">
            <!--titulo-->
            <flux:input name="title" wire:model="title" :label="__('Título')" type="text" autocomplete="title" placeholder="Digite o titulo" required />

            <!--conteudo-->
            <flux:textarea name="content" wire:model="content" :label="__('Conteúdo')"  placeholder="" />


            <div>
                @canany(['editar_posts', 'editar_qualquer_posts'])
                <flux:button type="submit" variant="primary" class="w-full cursor-pointer">
                    Salvar
                </flux:button>
                @endcanany
            </div>

        </form>
        


    </section>
</div>