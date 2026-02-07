@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Edit Community')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-black text-white italic tracking-tighter">EDIT COMMUNITY</h1>
            <a href="{{ route('admin.communities.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8">
            <form action="{{ route('admin.communities.update', $community) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="space-y-8">
                    <!-- Basic Info -->
                    <div>
                        <h3 class="text-neon font-bold text-lg mb-4 border-b border-white/10 pb-2">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Community Name</label>
                                <input type="text" name="name" value="{{ old('name', $community->name) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug', $community->slug) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">City</label>
                                <select name="city_id" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                    <option value="">Select City</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $community->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Theme Color</label>
                                <select name="theme_color" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon">
                                    <option value="neon" {{ old('theme_color', $community->theme_color) == 'neon' ? 'selected' : '' }}>Neon (Default)</option>
                                    <option value="dark" {{ old('theme_color', $community->theme_color) == 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ old('theme_color', $community->theme_color) == 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="red" {{ old('theme_color', $community->theme_color) == 'red' ? 'selected' : '' }}>Red</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-400 mb-2">Description</label>
                                <textarea name="description" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon">{{ old('description', $community->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Images & Links -->
                    <div>
                        <h3 class="text-neon font-bold text-lg mb-4 border-b border-white/10 pb-2">Images & Socials</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Logo</label>
                                @if($community->logo)
                                    <div class="mb-2 flex items-center gap-3">
                                        <img src="{{ asset('storage/' . $community->logo) }}" class="h-12 w-12 rounded-full object-cover">
                                        <label class="flex items-center gap-2 text-xs text-rose-400 cursor-pointer">
                                            <input type="checkbox" name="remove_logo" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-500 focus:ring-rose-500">
                                            Remove
                                        </label>
                                    </div>
                                @endif
                                <input type="file" name="logo" class="w-full text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-800 file:text-neon hover:file:bg-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Hero Image</label>
                                @if($community->hero_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $community->hero_image) }}" class="h-20 w-full object-cover rounded-lg mb-2">
                                        <label class="flex items-center gap-2 text-xs text-rose-400 cursor-pointer">
                                            <input type="checkbox" name="remove_hero_image" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-500 focus:ring-rose-500">
                                            Remove existing hero image
                                        </label>
                                    </div>
                                @endif
                                <input type="file" name="hero_image" class="w-full text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-800 file:text-neon hover:file:bg-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">WhatsApp Group Link</label>
                                <input type="url" name="wa_group_link" value="{{ old('wa_group_link', $community->wa_group_link) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="https://chat.whatsapp.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Instagram Link</label>
                                <input type="url" name="instagram_link" value="{{ old('instagram_link', $community->instagram_link) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="https://instagram.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">TikTok Link</label>
                                <input type="url" name="tiktok_link" value="{{ old('tiktok_link', $community->tiktok_link) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="https://tiktok.com/@...">
                            </div>
                        </div>
                    </div>

                    <!-- PIC Info -->
                    <div>
                        <h3 class="text-neon font-bold text-lg mb-4 border-b border-white/10 pb-2">PIC Information (Admin)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">PIC Name</label>
                                <input type="text" name="pic_name" value="{{ old('pic_name', $community->pic_name) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">PIC Email</label>
                                <input type="email" name="pic_email" value="{{ old('pic_email', $community->pic_email) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">PIC Phone</label>
                                <input type="text" name="pic_phone" value="{{ old('pic_phone', $community->pic_phone) }}" class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-neon" required>
                            </div>
                        </div>
                    </div>

                    <!-- Schedules -->
                    <div>
                        <div class="flex justify-between items-center mb-4 border-b border-white/10 pb-2">
                            <h3 class="text-neon font-bold text-lg">Weekly Schedules</h3>
                            <button type="button" id="add-schedule" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1 rounded-lg transition-colors">+ Add Schedule</button>
                        </div>
                        <div id="schedules-container" class="space-y-4">
                            @if(old('schedules', $community->schedules))
                                @foreach(old('schedules', $community->schedules) as $index => $schedule)
                                    <div class="p-4 rounded-xl bg-slate-800/50 border border-slate-700 relative schedule-row" id="schedule-row-{{ $index }}">
                                        <button type="button" class="absolute top-2 right-2 text-rose-500 hover:text-rose-400 remove-schedule" data-id="{{ $index }}">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <select name="schedules[{{ $index }}][day]" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                                    @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                                        <option value="{{ $day }}" {{ ($schedule['day'] ?? '') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <input type="text" name="schedules[{{ $index }}][time]" value="{{ $schedule['time'] ?? '' }}" placeholder="Time (19:00)" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                            </div>
                                            <div>
                                                <input type="text" name="schedules[{{ $index }}][activity]" value="{{ $schedule['activity'] ?? '' }}" placeholder="Activity" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                            </div>
                                            <div>
                                                <input type="text" name="schedules[{{ $index }}][location]" value="{{ $schedule['location'] ?? '' }}" placeholder="Location" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Captains -->
                    <div>
                        <div class="flex justify-between items-center mb-4 border-b border-white/10 pb-2">
                            <h3 class="text-neon font-bold text-lg">Captains / Pacer</h3>
                            <button type="button" id="add-captain" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1 rounded-lg transition-colors">+ Add Captain</button>
                        </div>
                        <div id="captains-container" class="space-y-4">
                            @if(old('captains', $community->captains))
                                @foreach(old('captains', $community->captains) as $index => $captain)
                                    <div class="p-4 rounded-xl bg-slate-800/50 border border-slate-700 relative captain-row" id="captain-row-{{ $index }}">
                                        <button type="button" class="absolute top-2 right-2 text-rose-500 hover:text-rose-400 remove-captain" data-id="{{ $index }}">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <input type="text" name="captains[{{ $index }}][name]" value="{{ $captain['name'] ?? '' }}" placeholder="Name" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                            </div>
                                            <div>
                                                <input type="text" name="captains[{{ $index }}][role]" value="{{ $captain['role'] ?? '' }}" placeholder="Role" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                            </div>
                                            <div>
                                                @if(isset($captain['image']) && is_string($captain['image']))
                                                    <div class="flex items-center justify-between mb-1">
                                                        <div class="text-xs text-slate-400 truncate max-w-[150px]">{{ basename($captain['image']) }}</div>
                                                        <label class="flex items-center gap-1 text-xs text-rose-400 cursor-pointer">
                                                            <input type="checkbox" name="captains[{{ $index }}][remove_image]" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-500 focus:ring-rose-500">
                                                            Remove
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="captains[{{ $index }}][existing_image]" value="{{ $captain['image'] }}">
                                                @endif
                                                <input type="file" name="captains[{{ $index }}][image]" class="w-full text-slate-400 text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-slate-900 file:text-neon">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- FAQs -->
                    <div>
                        <div class="flex justify-between items-center mb-4 border-b border-white/10 pb-2">
                            <h3 class="text-neon font-bold text-lg">FAQs</h3>
                            <button type="button" id="add-faq" class="text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-1 rounded-lg transition-colors">+ Add FAQ</button>
                        </div>
                        <div id="faqs-container" class="space-y-4">
                            @if(old('faqs', $community->faqs ?? []))
                                @foreach(old('faqs', $community->faqs ?? []) as $index => $faq)
                                    <div class="p-4 rounded-xl bg-slate-800/50 border border-slate-700 relative faq-row" id="faq-row-{{ $index }}">
                                        <button type="button" class="absolute top-2 right-2 text-rose-500 hover:text-rose-400 remove-faq" data-id="{{ $index }}">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                        <div class="space-y-3">
                                            <div>
                                                <input type="text" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="Question" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                            </div>
                                            <div>
                                                <textarea name="faqs[{{ $index }}][answer]" placeholder="Answer" rows="2" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">{{ $faq['answer'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-8 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-[0_0_20px_rgba(204,255,0,0.3)]">
                            UPDATE COMMUNITY
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let scheduleIndex = {{ count(old('schedules', $community->schedules ?? [])) }};
        const schedulesContainer = document.getElementById('schedules-container');
        document.getElementById('add-schedule').addEventListener('click', () => {
            const html = `
                <div class="p-4 rounded-xl bg-slate-800/50 border border-slate-700 relative schedule-row" id="schedule-row-${scheduleIndex}">
                    <button type="button" class="absolute top-2 right-2 text-rose-500 hover:text-rose-400 remove-schedule" data-id="${scheduleIndex}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <select name="schedules[${scheduleIndex}][day]" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div>
                            <input type="text" name="schedules[${scheduleIndex}][time]" placeholder="Time (19:00)" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <input type="text" name="schedules[${scheduleIndex}][activity]" placeholder="Activity" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <input type="text" name="schedules[${scheduleIndex}][location]" placeholder="Location" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                    </div>
                </div>
            `;
            schedulesContainer.insertAdjacentHTML('beforeend', html);
            scheduleIndex++;
        });

        schedulesContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-schedule')) {
                const id = e.target.closest('.remove-schedule').dataset.id;
                document.getElementById(`schedule-row-${id}`).remove();
            }
        });

        let captainIndex = {{ count(old('captains', $community->captains ?? [])) }};
        const captainsContainer = document.getElementById('captains-container');
        document.getElementById('add-captain').addEventListener('click', () => {
            const html = `
                <div class="p-4 rounded-xl bg-slate-800/50 border border-slate-700 relative captain-row" id="captain-row-${captainIndex}">
                    <button type="button" class="absolute top-2 right-2 text-rose-500 hover:text-rose-400 remove-captain" data-id="${captainIndex}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <input type="text" name="captains[${captainIndex}][name]" placeholder="Name" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <input type="text" name="captains[${captainIndex}][role]" placeholder="Role" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <input type="file" name="captains[${captainIndex}][image]" class="w-full text-slate-400 text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-slate-900 file:text-neon">
                        </div>
                    </div>
                </div>
            `;
            captainsContainer.insertAdjacentHTML('beforeend', html);
            captainIndex++;
        });

        captainsContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-captain')) {
                const id = e.target.closest('.remove-captain').dataset.id;
                document.getElementById(`captain-row-${id}`).remove();
            }
        });

        let faqIndex = {{ count(old('faqs', $community->faqs ?? [])) }};
        const faqsContainer = document.getElementById('faqs-container');
        document.getElementById('add-faq').addEventListener('click', () => {
            const html = `
                <div class="p-4 rounded-xl bg-slate-800/50 border border-slate-700 relative faq-row" id="faq-row-${faqIndex}">
                    <button type="button" class="absolute top-2 right-2 text-rose-500 hover:text-rose-400 remove-faq" data-id="${faqIndex}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    <div class="space-y-3">
                        <div>
                            <input type="text" name="faqs[${faqIndex}][question]" placeholder="Question" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <textarea name="faqs[${faqIndex}][answer]" placeholder="Answer" rows="2" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm"></textarea>
                        </div>
                    </div>
                </div>
            `;
            faqsContainer.insertAdjacentHTML('beforeend', html);
            faqIndex++;
        });

        faqsContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-faq')) {
                const id = e.target.closest('.remove-faq').dataset.id;
                document.getElementById(`faq-row-${id}`).remove();
            }
        });
    });
</script>
@endsection
