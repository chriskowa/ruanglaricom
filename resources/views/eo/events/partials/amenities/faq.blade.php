<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4" x-data="{ 
    faqs: {{ json_encode(array_values(old('premium_amenities.faq.items', isset($event) && isset($event->premium_amenities['faq']['items']) ? $event->premium_amenities['faq']['items'] : []))) }},
    addFaq() {
        this.faqs.push({ question: '', answer: '' });
    },
    removeFaq(index) {
        this.faqs.splice(index, 1);
    }
}">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[faq][enabled]" value="1" x-model="amenities.faq" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">FAQ Management</span>
        </label>
        <button type="button" x-show="amenities.faq" @click="addFaq()" class="text-xs font-bold text-yellow-400 hover:text-yellow-300 flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add FAQ
        </button>
    </div>

    <div x-show="amenities.faq" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-4">
        
        <template x-for="(faq, index) in faqs" :key="index">
            <div class="bg-slate-900/50 p-4 rounded-lg relative group border border-slate-800 hover:border-slate-700 transition-colors">
                <button type="button" @click="removeFaq(index)" class="absolute top-2 right-2 text-slate-600 hover:text-red-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Question</label>
                        <input type="text" :name="`premium_amenities[faq][items][${index}][question]`" x-model="faq.question" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Is there a cut-off time?" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Answer</label>
                        <textarea :name="`premium_amenities[faq][items][${index}][answer]`" x-model="faq.answer" rows="2" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="Yes, the COT is 2 hours." required></textarea>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="faqs.length === 0" class="text-center py-4 border-2 border-dashed border-slate-800 rounded-lg">
            <p class="text-sm text-slate-500">No FAQs added yet.</p>
            <p class="text-xs text-slate-600 mt-1">Click "Add FAQ" to start.</p>
        </div>
    </div>
</div>
