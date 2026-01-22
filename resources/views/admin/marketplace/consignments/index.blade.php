@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Marketplace Consignments</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($intakes as $intake)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                        @if(optional($intake->product->primaryImage)->image_path)
                                            <img src="{{ asset('storage/' . $intake->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $intake->product->title }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $intake->product_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $intake->seller->name }}</div>
                                <div class="text-xs text-gray-500">{{ $intake->seller->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ strtoupper($intake->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                @if($intake->status === 'requested')
                                    <form action="{{ route('admin.marketplace.consignments.received', $intake->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button class="px-3 py-1 rounded bg-blue-600 text-white text-sm">Mark Received</button>
                                    </form>
                                @endif
                                @if(in_array($intake->status, ['requested','received'], true))
                                    <form action="{{ route('admin.marketplace.consignments.listed', $intake->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button class="px-3 py-1 rounded bg-green-600 text-white text-sm">Publish Listing</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $intakes->links() }}
        </div>
    </div>
</div>
@endsection

