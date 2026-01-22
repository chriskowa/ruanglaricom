@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Marketplace Auctions</h2>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auction</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ends</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($auctions as $a)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                        @if(optional($a->primaryImage)->image_path)
                                            <img src="{{ asset('storage/' . $a->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $a->title }}</div>
                                        <div class="text-xs text-gray-500">Current: Rp {{ number_format($a->current_price ?? $a->starting_price ?? $a->price, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $a->seller->name }}</div>
                                <div class="text-xs text-gray-500">{{ $a->seller->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ strtoupper($a->auction_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $a->auction_end_at ? $a->auction_end_at->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.marketplace.auctions.show', $a->id) }}" class="px-3 py-1 rounded bg-gray-800 text-white text-sm">View</a>
                                @if($a->auction_status === 'running')
                                    <form action="{{ route('admin.marketplace.auctions.finalize', $a->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button class="px-3 py-1 rounded bg-green-600 text-white text-sm">Finalize</button>
                                    </form>
                                    <form action="{{ route('admin.marketplace.auctions.cancel', $a->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button class="px-3 py-1 rounded bg-red-600 text-white text-sm">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $auctions->links() }}
        </div>
    </div>
</div>
@endsection

