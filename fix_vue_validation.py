import re

file_path = r'c:\laragon\www\ruanglari\resources\js\Components\RunConnect\CreateRunThreadModal.vue'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add description error tag
target1 = r"""                        <textarea 
                            v-model="form\.description" 
                            rows="3"
                            placeholder="Jelaskan detail rute, meeting point spesifik, atau info penting lainnya\.\.\."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-\[#ccff00\]"
                        ></textarea>"""

replacement1 = r"""                        <textarea 
                            v-model="form.description" 
                            rows="3"
                            maxlength="500"
                            placeholder="Jelaskan detail rute, meeting point spesifik, atau info penting lainnya..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        ></textarea>
                        <p v-if="errors.description" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.description) ? errors.description[0] : errors.description }}</p>"""

content = re.sub(target1, replacement1, content)

# 2. Skip appending empty strings to FormData
target2 = r"""            \} else if \(form\[key\] !== null && form\[key\] !== undefined\) \{
                formData\.append\(key, form\[key\]\);
            \}"""

replacement2 = r"""            } else if (form[key] !== null && form[key] !== undefined && form[key] !== '') {
                formData.append(key, form[key]);
            }"""

content = re.sub(target2, replacement2, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("CreateRunThreadModal.vue updated: description error tag added and FormData fixed.")
