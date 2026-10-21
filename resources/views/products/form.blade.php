<x-user-layout>
    <x-panel :header="$product->exists ? 'Sửa sản phẩm' : 'Thêm sản phẩm'">
        @if($errors->any())<div role="alert" class="text-red-600 mb-4">{{ $errors->first() }}</div>@endif
        <form class="grid gap-5 max-w-2xl text-on-surface-600" method="POST" action="{{ $product->exists ? route('manage.products.update', $product) : route('manage.products.store') }}">
            @csrf
            @if($product->exists) @method('PUT') @endif
            <label>Tên sản phẩm<input class="block border rounded p-2 w-full" name="name" value="{{ old('name', $product->name) }}" maxlength="255" required></label>
            <label>Mô tả<textarea class="block border rounded p-2 w-full" name="description" maxlength="255" required>{{ old('description', $product->description) }}</textarea></label>
            <label>Giá<input class="block border rounded p-2 w-full" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('price', $product->price) }}" required></label>
            <label>Tiền tệ<input class="block border rounded p-2 w-full" name="currency" value="{{ old('currency', $product->currency) }}" minlength="3" maxlength="3" required></label>
            <label>URL ảnh<input class="block border rounded p-2 w-full" name="display_image_url" type="url" maxlength="255" value="{{ old('display_image_url', $product->display_image_url) }}"></label>
            <label>Danh mục<select class="block border rounded p-2 w-full bg-surface" name="category_id">
                <option value="">Chưa phân loại</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select></label>
            <div class="flex gap-4 items-center"><x-button type="submit">Lưu sản phẩm</x-button><a class="underline" href="{{ route('manage.products.index') }}">Hủy</a></div>
        </form>
    </x-panel>
</x-user-layout>
