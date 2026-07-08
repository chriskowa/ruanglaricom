import re

controller_path = r'c:\laragon\www\ruanglari\app\Http\Controllers\RunConnectController.php'

with open(controller_path, 'r', encoding='utf-8') as f:
    controller_content = f.read()

store_thread_regex = r"(\s*'is_recurring' => 'boolean',\s*'notes' => 'nullable\|string\|max:500',\s*\]\);\s*if \(\$validator->fails\(\)\) \{\s*return response\(\)->json\(\['errors' => \$validator->errors\(\)\], 422\);\s*\})"

def replace_store_thread(m):
    return r"""            'is_recurring' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'gpx_file' => 'nullable|file|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }"""

controller_content = re.sub(store_thread_regex, replace_store_thread, controller_content)

store_save_regex = r"(\$data\['status'\] = 'open';\s*\$thread = RunThread::create\(\$data\);)"

def replace_store_save(m):
    return r"""$data['status'] = 'open';

        // Extract gpx_file from data before creating
        $gpxFile = null;
        if ($request->hasFile('gpx_file')) {
            $gpxFile = $request->file('gpx_file');
            unset($data['gpx_file']);
        }

        $thread = RunThread::create($data);

        // Store GPX file if provided
        if ($gpxFile) {
            $path = $gpxFile->store('gpx', 'public');
            $thread->update(['gpx_file_path' => '/storage/' . $path]);
        }"""

controller_content = re.sub(store_save_regex, replace_store_save, controller_content)

ai_method = r"""
    /**
     * Generate an AI description for a run thread
     */
    public function generateAiDescription(Request $request, \App\Services\OpenAiService $openAiService)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'type' => 'nullable|string',
            'distance' => 'nullable|numeric'
        ]);

        $title = $request->input('title');
        $type = $request->input('type', 'Casual Run');
        $distance = $request->input('distance', 0);
        
        $prompt = "Tolong buatkan deskripsi singkat, menarik, dan friendly untuk acara lari bersama (running thread). ";
        $prompt .= "Judul: {$title}. Tipe Lari: {$type}. ";
        if ($distance > 0) {
            $prompt .= "Jarak: {$distance} km. ";
        }
        $prompt .= "Tuliskan dalam 2-3 paragraf pendek. Gunakan bahasa Indonesia kasual, semangat, mengundang orang untuk ikut, dan sebutkan bahwa ini terbuka untuk komunitas. Tidak perlu berlebihan, cukup padat dan jelas.";
        
        $system = "Anda adalah asisten AI Ruang Lari yang bertugas menulis deskripsi acara lari yang asyik dan memotivasi. Dilarang menggunakan format markdown rumit, cukup teks biasa dengan enter/paragraf, dan gunakan emoji secukupnya.";
        
        try {
            $description = $openAiService->getAiResponseOrThrow($prompt, $system);
            
            // Clean markdown tags if OpenAI accidentally returns them
            $description = preg_replace('/^```(?:html|text)?\s*/i', '', $description);
            $description = preg_replace('/```$/', '', $description);
            $description = trim($description);
            
            return response()->json(['description' => $description]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal generate AI: ' . $e->getMessage()], 500);
        }
    }
"""

def replace_ai_method(m):
    return ai_method + m.group(1)

controller_content = re.sub(r"(\s*/\*\*\s*\*\s*Upload GPX file for a thread \(creator only\)\s*\*/)", replace_ai_method, controller_content)

with open(controller_path, 'w', encoding='utf-8') as f:
    f.write(controller_content)

print("RunConnectController updated successfully.")

routes_path = r'c:\laragon\www\ruanglari\routes\web.php'
with open(routes_path, 'r', encoding='utf-8') as f:
    routes_content = f.read()

if "generate-description" not in routes_content:
    routes_replacement = r"""Route::post('/api/run-connect/threads', [RunConnectController::class, 'storeThread'])->name('api.run-connect.store');
Route::post('/api/run-connect/generate-description', [RunConnectController::class, 'generateAiDescription'])->middleware('throttle:10,1')->name('api.run-connect.generate-description');"""
    routes_content = re.sub(r"Route::post\('/api/run-connect/threads', \[RunConnectController::class, 'storeThread'\]\)->name\('api\.run-connect\.store'\);", lambda m: routes_replacement, routes_content)
    with open(routes_path, 'w', encoding='utf-8') as f:
        f.write(routes_content)
    print("Routes updated successfully.")
else:
    print("Routes already updated.")
