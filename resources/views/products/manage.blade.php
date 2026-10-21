<x-user-layout>
    <x-panel header="Quản lý sản phẩm">
        <div class="mb-6 flex flex-wrap gap-4 text-primary-600">
            <a class="underline" href="{{ route('manage.products.create') }}">+ Thêm sản phẩm</a>
            <a class="underline" href="{{ route('product-images.index') }}">Upload ảnh</a>
            <a class="underline" href="{{ route('dashboard') }}">Dashboard</a>
        </div>
        @if(session('status'))<p class="mb-4 text-on-surface-600" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<p class="mb-4 text-red-600" role="alert">{{ $errors->first() }}</p>@endif
        <div class="overflow-x-auto text-on-surface-600">
            <table class="w-full text-left">
                <thead><tr><th class="p-3">Sản phẩm</th><th class="p-3">Danh mục</th><th class="p-3">Giá</th><th class="p-3">Thao tác</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-t border-on-surface">
                            <td class="p-3">{{ $product->name }}</td>
                            <td class="p-3">{{ $product->category?->name ?? 'Chưa phân loại' }}</td>
                            <td class="p-3 whitespace-nowrap">{{ $product->formatted_price }}</td>
                            <td class="p-3"><div class="flex items-center gap-4">
                                <a class="underline text-primary-600" href="{{ route('manage.products.edit', $product) }}">Sửa</a>
                                <form method="POST" action="{{ route('manage.products.destroy', $product) }}" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 underline" type="submit">Xóa</button>
                                </form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td class="p-3" colspan="4">Chưa có sản phẩm.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </x-panel>
</x-user-layout>
