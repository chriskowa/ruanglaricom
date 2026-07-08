import re

file_path = r'c:\laragon\www\ruanglari\resources\js\Components\RunConnect\CreateRunThreadModal.vue'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update initialForm to include gpx_file
initial_form_regex = r"(\s*notes: ''\s*)\};"
initial_form_replace = lambda m: r""",
    gpx_file: null""" + m.group(1) + "};"
content = re.sub(initial_form_regex, initial_form_replace, content)

# 2. Update paces array
paces_regex = r"const paces = \['4:00', '4:30', '5:00', '5:30', '6:00', '6:30', '7:00', '7:30', '8:00', 'Bebas'\];"
paces_replace = r"const paces = ['3:00', '3:30', '4:00', '4:30', '5:00', '5:30', '6:00', '6:30', '7:00', '7:30', '8:00', 'Bebas'];"
content = re.sub(paces_regex, paces_replace, content)

# 3. Modify submitForm to use FormData
submit_form_regex = r"(const payload = \{[\s\S]*?is_recurring: form\.is_recurring \? 1 : 0,\s*\};\s*let res;\s*if \(props\.editThread\) \{[\s\S]*?res = await axios\.put\(`/api/run-connect/threads/\$\{props\.editThread\.id\}`,\s*payload\);[\s\S]*?\} else \{[\s\S]*?res = await axios\.post\('/api/run-connect/threads',\s*payload\);[\s\S]*?\})"

def replace_submit_form(m):
    return r"""const formData = new FormData();
        Object.keys(form).forEach(key => {
            if (key === 'is_beginner_friendly' || key === 'is_women_friendly' || key === 'is_recurring') {
                formData.append(key, form[key] ? '1' : '0');
            } else if (key === 'gpx_file') {
                if (form[key]) {
                    formData.append(key, form[key]);
                }
            } else if (form[key] !== null && form[key] !== undefined) {
                formData.append(key, form[key]);
            }
        });

        let res;
        if (props.editThread) {
            formData.append('_method', 'PUT');
            res = await axios.post(`/api/run-connect/threads/${props.editThread.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            emit('updated', res.data.thread);
        } else {
            res = await axios.post('/api/run-connect/threads', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            emit('created', res.data.thread);
        }"""

content = re.sub(submit_form_regex, replace_submit_form, content)

# 4. Add AI Generate function and state
ai_generate_func = r"""
const isGeneratingAi = ref(false);

const generateDescription = async () => {
    if (!form.title) {
        alert('Mohon isi Judul Acara terlebih dahulu sebelum menggunakan AI.');
        return;
    }
    
    isGeneratingAi.value = true;
    try {
        const res = await axios.post('/api/run-connect/generate-description', {
            title: form.title,
            type: form.type,
            distance: form.run_distance_km
        });
        
        if (res.data && res.data.description) {
            form.description = res.data.description;
        }
    } catch (err) {
        alert('Gagal membuat deskripsi dengan AI. Silakan coba lagi.');
        console.error(err);
    } finally {
        isGeneratingAi.value = false;
    }
};

"""
# insert before resetForm
content = re.sub(r"(const resetForm = \(\) => \{)", lambda m: ai_generate_func + m.group(1), content)

# 5. Add GPX HTML input in step 4
gpx_html = r"""
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2">Upload Rute (GPX / FIT)</label>
                        <input 
                            type="file" 
                            accept=".gpx,.fit"
                            @change="e => form.gpx_file = e.target.files[0]"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2 text-sm text-slate-800 dark:text-slate-200 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-[#ccff00]/10 dark:file:text-[#ccff00] dark:hover:file:bg-[#ccff00]/20"
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Opsional: Upload file rute agar peserta bisa melihat rute di peta atau mendownloadnya ke smartwatch mereka.</p>
                    </div>
"""
# insert before catatan rute
content = re.sub(r"(<h4 class=\"text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider \s*mb-2\">Catatan Rute & Publikasi</h4>\s*<div>)", lambda m: m.group(1).replace("<div>", gpx_html + "\n                    <div>"), content)

# 6. Add AI button near description in step 1
ai_button_html = r"""
                        <div class="flex justify-between items-end mb-2">
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase">Deskripsi / Detail Lari (Opsional)</label>
                            <button type="button" @click="generateDescription" :disabled="isGeneratingAi || !form.title" class="text-[10px] flex items-center gap-1 font-bold bg-purple-100 text-purple-600 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50 px-2 py-1 rounded-full transition-colors disabled:opacity-50">
                                <svg v-if="isGeneratingAi" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-else>✨</span>
                                AI Generate
                            </button>
                        </div>
"""
content = re.sub(r"(<label class=\"block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2\">Deskripsi / Detail Lari \(Opsional\)</label>)", lambda m: ai_button_html, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("CreateRunThreadModal.vue updated successfully.")
