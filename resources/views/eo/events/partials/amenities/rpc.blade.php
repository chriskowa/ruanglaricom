<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[rpc][enabled]" value="1" x-model="amenities.rpc" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">Race Pack Collection</span>
        </label>
    </div>

    <div x-show="amenities.rpc" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Location Name</label>
                <input type="text" name="rpc_location_name" value="{{ old('rpc_location_name', isset($event) ? $event->rpc_location_name : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Mall FX Sudirman">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Full Address</label>
                <input type="text" name="rpc_location_address" value="{{ old('rpc_location_address', isset($event) ? $event->rpc_location_address : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="Street address...">
            </div>
            <input type="hidden" name="rpc_latitude" value="{{ old('rpc_latitude', isset($event) ? $event->rpc_latitude : '') }}">
            <input type="hidden" name="rpc_longitude" value="{{ old('rpc_longitude', isset($event) ? $event->rpc_longitude : '') }}">
        </div>
    </div>
</div>
