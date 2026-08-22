@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Blast Email - ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <!-- Header -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('eo.events.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Events</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Blast Email</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    BLAST <span class="text-yellow-400">EMAIL</span>
                </h1>
                <p class="text-slate-400 mt-2">Send announcements to participants of {{ $event->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('eo.events.show', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center gap-2 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    Preview
                </a>
                <a href="{{ route('eo.events.edit', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center gap-2 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Edit Event
                </a>
                <a href="{{ route('eo.events.participants', $event) }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center gap-2 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Participants
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8 relative z-10 max-w-4xl">
        <form action="{{ route('eo.events.blast.send', $event->id) }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-300 mb-2">Subject <span class="text-red-400">*</span></label>
                <div class="flex">
                    <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-slate-700 bg-slate-800 text-slate-400 text-sm">
                        [{{ $event->name }}]
                    </span>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="flex-1 bg-slate-900 border border-slate-700 rounded-r-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Important Announcement: Race Pack Collection" required>
                </div>
                @error('subject') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-300 mb-2">Target Audience</label>
                <select name="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                    <option value="">All Participants (Paid)</option>
                    @foreach($event->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->distance_km }}KM)</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Only participants with "PAID" status will receive this email.</p>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-medium text-slate-300 mb-2">Message Content <span class="text-red-400">*</span></label>
                <div class="rounded-xl overflow-hidden">
                    <div id="content_editor"></div>
                    <textarea name="content" id="content" class="hidden">{{ old('content') }}</textarea>
                </div>
                @error('content') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-700">
                <div class="text-sm text-slate-400">
                    @if($event->is_instant_notification)
                        <span class="flex items-center text-yellow-400 gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Instant Sending Enabled
                        </span>
                    @else
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Queued Sending (Background)
                        </span>
                    @endif
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('eo.events.index') }}" class="px-6 py-3 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-800 transition-colors font-bold">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black shadow-lg shadow-yellow-500/20 transition-all transform hover:scale-105 flex items-center gap-2" onclick="return confirm('Are you sure you want to send this email to all selected participants?')">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Send Blast Email
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
    <style>
        /* CKEditor 5 Solid Dark Theme Styling */
        .ck.ck-editor {
            width: 100% !important;
        }
        .ck-editor__editable_inline {
            min-height: 250px !important;
            max-height: 850px !important;
            background-color: #0c121e !important;
            color: #f8fafc !important;
            border-radius: 0 0 0.75rem 0.75rem !important;
            padding: 1.25rem !important;
            border-color: #334155 !important;
            font-size: 0.875rem !important;
            line-height: 1.6 !important;
        }
        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-color: #334155 !important;
        }
        .ck.ck-editor__main>.ck-editor__editable.ck-focused {
            border-color: #eab308 !important;
            box-shadow: 0 0 0 1px #eab308 !important;
        }
        .ck.ck-toolbar {
            background-color: #111724 !important;
            border-color: #334155 !important;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 0.25rem !important;
        }
        .ck.ck-toolbar .ck-toolbar__separator {
            background-color: #334155 !important;
        }
        .ck.ck-toolbar .ck-button {
            color: #cbd5e1 !important;
            cursor: pointer !important;
        }
        .ck.ck-toolbar .ck-button:hover:not(.ck-disabled),
        .ck.ck-toolbar .ck-button:focus:not(.ck-disabled) {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }
        .ck.ck-toolbar .ck-button.ck-on {
            background-color: #eab308 !important;
            color: #0f172a !important;
        }
        .ck.ck-toolbar .ck-button.ck-on .ck-icon {
            color: #0f172a !important;
        }
        .ck.ck-dropdown__panel {
            background-color: #111724 !important;
            border-color: #334155 !important;
        }
        .ck.ck-list {
            background-color: #111724 !important;
        }
        .ck.ck-list__item button {
            color: #f8fafc !important;
        }
        .ck.ck-list__item button:hover:not(.ck-disabled) {
            background-color: #1e293b !important;
            color: #eab308 !important;
        }
        .ck.ck-list__item button.ck-on {
            background-color: #eab308 !important;
            color: #0f172a !important;
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        ClassicEditor
            .create(document.querySelector('#content_editor'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
            })
            .then(editor => {
                editor.setData(`{!! old('content') !!}`);
                editor.model.document.on('change:data', () => {
                    document.querySelector('#content').value = editor.getData();
                });
            })
            .catch(error => console.error(error));
    });
</script>
@endpush
