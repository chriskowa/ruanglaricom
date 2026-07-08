import re

file_path = r'c:\laragon\www\ruanglari\resources\js\Components\RunConnect\CreateRunThreadModal.vue'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = r"""        if \(!form\.start_date\) \{ errors\.value\.start_date = "Tanggal start wajib diisi\."; return; \}
        if \(!form\.start_time\) \{ errors\.value\.start_time = "Jam start wajib diisi\."; return; \}"""

replacement = r"""        if (!form.start_date) { errors.value.start_date = "Tanggal start wajib diisi."; return; }
        if (!form.start_time) { errors.value.start_time = "Jam start wajib diisi."; return; }
        
        const now = new Date();
        const startDateTime = new Date(`${form.start_date}T${form.start_time}:00`);
        if (startDateTime <= new Date(now.getTime() + 60000)) {
            errors.value.start_time = "Waktu mulai harus di masa depan (minimal 1 menit dari sekarang).";
            return;
        }"""

content = re.sub(target, lambda m: replacement, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added frontend validation for start_time.")
