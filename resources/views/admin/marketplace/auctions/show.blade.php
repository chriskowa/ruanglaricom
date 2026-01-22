@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold">Auction Detail</h2>
            <div class="text-sm text-gray-500">{{ $product->title }}</div>
        </div>
        <a href="{{ route('admin.marketplace.auctions.index') }}" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">Back</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-xs text-gray-500 uppercase tracking-wider">Status</div>
                <div class="font-semibold">{{ strtoupper($product->auction_status) }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 uppercase tracking-wider">Ends</div>
                <div class="font-semibold">{{ $product->auction_end_at ? $product->auction_end_at->format('Y-m-d H:i') : '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 uppercase tracking-wider">Winner</div>
                <div class="font-semibold">{{ $product->auction_winner_id ? $product->auction_winner_id : '-' }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 font-semibold">Bids</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidder</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bids as $bid)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $bid->bidder->name ?? 'User' }}</div>
                                <div class="text-xs text-gray-500">{{ $bid->bidder->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold">Rp {{ number_format($bid->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $bid->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $bids->links() }}
        </div>
    </div>
</div>
@endsection

