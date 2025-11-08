@extends('layouts.app')

@section('title', __('messages.create_commission_tier'))

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.commission-tiers.index') }}" class="text-blue-600 hover:text-blue-800">
                ← {{ __('messages.back_to_commission_tiers') }}
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.create_commission_tier') }}</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.commission-tiers.store') }}" method="POST">
                    @include('admin.commission-tiers._form', ['submitLabel' => __('messages.save_tier')])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

