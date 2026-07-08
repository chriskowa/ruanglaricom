import re

file_path = r'c:\laragon\www\ruanglari\resources\js\Components\RunConnect\CreateRunThreadModal.vue'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = r'<label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase \s*mb-2">Deskripsi Singkat \(Opsional\)</label>'

ai_button_html = r"""<div class="flex justify-between items-end mb-2">
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase">Deskripsi Singkat (Opsional)</label>
                            <button type="button" @click="generateDescription" :disabled="isGeneratingAi || !form.title" class="text-[10px] flex items-center gap-1 font-bold bg-purple-100 text-purple-600 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50 px-2 py-1 rounded-full transition-colors disabled:opacity-50">
                                <svg v-if="isGeneratingAi" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-else>✨</span>
                                AI Generate
                            </button>
                        </div>"""

content = re.sub(target, ai_button_html, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("CreateRunThreadModal.vue updated with AI button.")
