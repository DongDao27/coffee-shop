<x-user-layout>
    <x-slot name="page_title">
        {{ __('Dashboard') }}
    </x-slot>

    <section>
        <x-panel :header="__('Dashboard')">
            <div class="flex flex-wrap gap-4 mb-5 text-primary-600">
                <a class="underline" href="{{ route('manage.products.index') }}">Quản lý sản phẩm</a>
                <a class="underline" href="{{ route('apiTokens.index') }}">API Tokens</a>
            </div>
            <a class="text-primary-600 underline" href="{{ route('product-images.index') }}">{{ __('Product images') }}</a>
            <div class="text-on-surface-600">
                <span>{{ __('Hello') }}</span>
                <b>{{ auth()->user()->name }},</b>
                <span>{{ __("You're logged in!") }}</span>
            </div>
        </x-panel>
    </section>
</x-user-layout>
