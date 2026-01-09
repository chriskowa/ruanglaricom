<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4" x-data="{ 
    items: {{ json_encode(old('premium_amenities.what_to_bring.items', isset($event) && isset($event->premium_amenities['what_to_bring']['items']) ? $event->premium_amenities['what_to_bring']['items'] : [])) }},
    addItem() {
        this.items.push('');
    },
    removeItem(index) {
        this.items.splice(index, 1);
    }
}">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[what_to_bring][enabled]" value="1" x-model="amenities.what_to_bring" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">What To Bring</span>
        </label>
        <button type="button" x-show="amenities.what_to_bring" @click="addItem()" class="text-xs font-bold text-yellow-400 hover:text-yellow-300 flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add Item
        </button>
    </div>

    <div x-show="amenities.what_to_bring" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-3">
        
        <template x-for="(item, index) in items" :key="index">
            <div class="flex gap-2">
                <input type="text" :name="`premium_amenities[what_to_bring][items][]`" x-model="items[index]" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Running Shoes">
                <button type="button" @click="removeItem(index)" class="text-slate-500 hover:text-red-400 transition-colors p-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        </template>

        <div x-show="items.length === 0" class="text-center py-4 border-2 border-dashed border-slate-800 rounded-lg">
            <p class="text-sm text-slate-500">No items added yet.</p>
        </div>
    </div>
</div>
