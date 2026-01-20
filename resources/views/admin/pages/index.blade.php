@extends('layouts.pacerhub')
@php($withSidebar = true)
@section('title', 'Manage Pages')

@section('content')
<div class="p-8 bg-slate-900 rounded-xl overflow-hidden shadow-xl border border-slate-700 pt-20">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white">Pages Management</h2>
        <a href="{{ route('admin.pages.create') }}" class="bg-neon text-dark font-bold py-2 px-4 rounded hover:bg-neon/90 transition">Add New Page</a>
    </div>

    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500 text-green-100 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-slate-300">
            <thead class="bg-slate-800 text-slate-100 uppercase text-sm">
                <tr>
                    <th class="py-3 px-4">Title</th>
                    <th class="py-3 px-4">Slug</th>
                    <th class="py-3 px-4">Hardcoded</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($pages as $page)
                <tr class="hover:bg-slate-800/50">
                    <td class="py-3 px-4 font-medium text-white">{{ $page->title }}</td>
                    <td class="py-3 px-4 text-sm">{{ $page->slug }}</td>
                    <td class="py-3 px-4 text-sm">
                        @if($page->hardcoded)
                            <span class="bg-blue-600/20 text-blue-400 py-1 px-2 rounded text-xs">{{ $page->hardcoded }}</span>
                        @else
                            <span class="text-slate-500">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @if($page->status === 'published')
                            <span class="bg-green-600/20 text-green-400 py-1 px-2 rounded text-xs">Published</span>
                        @elseif($page->status === 'archived')
                            <span class="bg-yellow-600/20 text-yellow-400 py-1 px-2 rounded text-xs">Archived</span>
                        @else
                            <span class="bg-red-600/20 text-red-400 py-1 px-2 rounded text-xs">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right space-x-2">
                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="text-slate-400 hover:text-white" title="View"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-yellow-400 hover:text-yellow-300" title="Edit"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $pages->links() }}
    </div>
</div>
@endsection
