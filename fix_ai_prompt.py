import re

file_path = r'c:\laragon\www\ruanglari\app\Http\Controllers\RunConnectController.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = r"""        \$prompt \.= "Tuliskan dalam 2-3 paragraf pendek\. Gunakan bahasa Indonesia kasual, semangat, mengundang orang \r?\n?ikut, dan sebutkan bahwa ini terbuka untuk komunitas\. Tidak perlu berlebihan, cukup padat dan jelas\.";"""

replacement = r"""        $prompt .= "Tuliskan maksimal 300 karakter. Gunakan bahasa Indonesia kasual, semangat, mengundang orang ikut, dan sebutkan terbuka untuk komunitas. Tidak perlu berlebihan, harus padat dan jelas.";"""

# If the exact text isn't found due to wrapping, let's use a simpler replace
content = re.sub(r'Tuliskan dalam 2-3 paragraf pendek\..+?cukup padat dan jelas\.', 
                 r'Tuliskan maksimal 300 karakter. Gunakan bahasa Indonesia kasual, semangat, mengundang orang ikut. Tidak perlu berlebihan, harus padat dan jelas.', 
                 content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("AI prompt updated.")
