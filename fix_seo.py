import re

file_path = r'c:\laragon\www\ruanglari\resources\views\tools\calculator.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update SEO Meta Tags
seo_block = """@section('title', 'Kalkulator Lari Terlengkap | Ruang Lari Tools')
@section('meta_title', 'Kalkulator Lari Terlengkap: Pace, VO2 Max, Marathon, Magic Mile | Ruang Lari')
@section('meta_description', 'Gunakan Kalkulator Ruang Lari untuk menghitung Pace, VO2 Max, Magic Mile, Prediksi Waktu Marathon, Splits, Kebutuhan Hidrasi, Fueling Karbohidrat, hingga Smart Mileage Builder. Alat wajib untuk pelari pemula hingga profesional.')
@section('meta_keywords', 'kalkulator lari, kalkulator pace, kalkulator vo2 max, prediksi marathon, magic mile, kalkulator split lari, smart mileage builder, kalkulator hidrasi pelari, kalkulator fueling marathon, ruang lari tools')
@section('og_title', 'Kalkulator Lari Terlengkap | Ruang Lari Tools')
@section('og_description', 'Hitung Pace, VO2 Max, Magic Mile, Waktu Marathon, Hidrasi, dan Fueling Karbohidrat secara akurat menggunakan Kalkulator Ruang Lari.')"""

content = re.sub(r"@section\('title',\s*'Ruang Lari Tools - Calculator'\)", seo_block, content)

# 2. Update H1 CSS to remove gradient
old_h1_css = r"""        #rl-calculator .rlc-header h1 \{
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient\(135deg, rgba\(204,255,0,\.95\) 0%, rgba\(6,182,212,\.95\) 50%, rgba\(59,130,246,\.95\) 100%\);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: \.5rem;
            line-height: 1\.2;
        \}"""

new_h1_css = """        #rl-calculator .rlc-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: .5rem;
            line-height: 1.2;
        }"""

content = re.sub(old_h1_css, new_h1_css, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("SEO tags and H1 color updated.")
