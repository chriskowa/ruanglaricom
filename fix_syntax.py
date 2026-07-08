import re

file_path = r'c:\laragon\www\ruanglari\resources\js\Components\RunConnect\CreateRunThreadModal.vue'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix syntax error in CreateRunThreadModal.vue initialForm
fix_regex = r"(is_recurring: false,,?\s*gpx_file: null\s*notes: '')"

content = re.sub(fix_regex, "is_recurring: false,\n      gpx_file: null,\n      notes: ''", content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Syntax fixed.")
