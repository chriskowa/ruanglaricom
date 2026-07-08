import re

file_path = r'c:\laragon\www\ruanglari\app\Http\Controllers\RunConnectController.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = r"""        if \(\$validator->fails\(\)\) \{
            return response\(\)->json\(\['errors' => \$validator->errors\(\)\], 422\);
        \}"""

replacement = r"""        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('RunConnect Validation Failed: ', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }"""

content = re.sub(target, lambda m: replacement, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added validation logging.")
