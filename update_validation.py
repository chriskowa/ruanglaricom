import re

file_path = r'c:\laragon\www\ruanglari\app\Http\Controllers\RunConnectController.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

validation_check = r"""        if \(\$validator->fails\(\)\) \{
            return response\(\)->json\(\['errors' => \$validator->errors\(\)\], 422\);
        \}"""

validation_replace = r"""        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('start_date') && $request->has('start_time')) {
            try {
                $startDateTime = \Carbon\Carbon::parse($request->start_date . ' ' . $request->start_time);
                if ($startDateTime->lessThanOrEqualTo(now()->addMinutes(1))) {
                    return response()->json(['errors' => ['start_time' => ['Waktu mulai acara lari harus di masa depan (minimal 1 menit dari sekarang).']]], 422);
                }
            } catch (\Exception $e) {
                // Ignore parse errors, let standard validation handle it
            }
        }"""

content = re.sub(validation_check, lambda m: validation_replace, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("RunConnectController validation updated successfully.")
