<x-user-layout>
    <x-panel :header="__('Product images')">
        @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<p role="alert" class="text-red-600">{{ $errors->first() }}</p>@endif
        <div class="grid gap-6">
            @foreach($products as $product)
                <form action="{{ route('product-images.update', $product) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-4 text-on-surface-600">
                    @csrf @method('PUT')
                    <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}" class="w-20 h-20 object-cover rounded">
                    <label>{{ $product->name }}<input class="block" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label>
                    <x-button type="submit">{{ __('Upload image') }}</x-button>
                </form>
            @endforeach
        </div>
        {{ $products->links() }}
    </x-panel>
</x-user-layout>
