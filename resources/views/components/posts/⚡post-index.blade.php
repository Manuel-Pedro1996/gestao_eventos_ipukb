<?php

use Livewire\Component;
use App\Models\Post;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

     public function rendering($view){
        $view->title('Posts');
    }

    public function deletePost($postId){

        $post = Post::findOrFail($postId);
        $post->delete();
    }

    public function with()
    {
        
        return  [
            'posts' => Post::latest()->paginate(10),
    ];
    }
};
?>

 
 <div class="p-6 w-full">
    <div class="flex justify-between mb-4">
        <div>
            <flux:heading size="xl"> Post Index</flux:heading>
            <flux:subheading size="lg"> List of Post</flux:subheading>    
        </div> 
        <div>
            @canany(['criar_posts', 'criar_qualquer_posts'])
            <flux:button  href="{{ route('posts.create') }}" icon:trailing="arrow-up-right">
                New Post
            </flux:button>
            @endcanany
        </div>
    </div>
    <flux:separator class="my-4" />  

<div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
    <table class="w-full text-sm text-left rtl:text-right text-body">
        <thead class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-default">
            <tr>
                <th scope="col" class="px-6 py-3"> 
                    ID
                </th>
                <th scope="col" class="px-6 py-3">
                    Título
                </th>
                <th scope="col" class="px-6 py-3">
                    Conteudo
                </th>
                <th scope="col" class="px-6 py-3">
                    Autor 
                </th>
                <th scope="col" class="px-6 py-3">
                    Ações
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    {{ $post->id }}
                </th>
                <td class="px-6 py-4">
                    {{ $post->title }}
                </td>
                <td>
                    {{ $post->content }}
                </td>

                <td class="px-6 py-4">
                    {{ $post->autor->name }}
                </td>
                <td class="px-6 py-4">
                    @canany(['editar_posts'])
                    <flux:button href="{{ route('posts.edit', $post->id) }}" size="sm" variant="primary" color="green" icon:trailing="pencil">
                        Editar
                    </flux:button>
                    @endcanany

                    @canany(['eliminar_posts'])
                    <flux:button wire:click="deletePost({{ $post->id }})" size="sm" variant="danger" wire:confirm="Confirmar  exclusão do Post" color="red" icon:trailing="trash">
                        Deletar
                    </flux:button>
                    @endcanany
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="p-4">
        {{ $posts->links() }}
</div>
 </div>