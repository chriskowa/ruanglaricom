import re

file_path = r'c:\laragon\www\ruanglari\resources\js\Components\RunConnect\CreateRunThreadModal.vue'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = r"""            if \(err\.response && err\.response\.data && err\.response\.data\.errors\) \{
                errors\.value = err\.response\.data\.errors;
                if \(errors\.value\.title \|\| errors\.value\.description \|\| errors\.value\.type\) \{
                    step\.value = 1;
                \} else if \(errors\.value\.start_location_name \|\| errors\.value\.start_latitude \|\| errors\.value\.start_longitude \|\| errors\.value\.start_date \|\| errors\.value\.start_time\) \{
                    step\.value = 2;
                \} else \{
                    step\.value = 3;
                \}"""

replacement = r"""            if (err.response && err.response.data && err.response.data.errors) {
                errors.value = err.response.data.errors;
                if (errors.value.title || errors.value.description || errors.value.type) {
                    step.value = 1;
                } else if (errors.value.start_location_name || errors.value.start_latitude || errors.value.start_longitude || errors.value.start_date || errors.value.start_time) {
                    step.value = 2;
                } else if (errors.value.run_distance_km || errors.value.quota || errors.value.pace_min || errors.value.pace_max) {
                    step.value = 3;
                } else {
                    step.value = 4;
                }"""

content = re.sub(target, lambda m: replacement, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed step validation navigation.")
