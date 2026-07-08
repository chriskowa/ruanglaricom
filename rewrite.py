import re

file_path = r'c:\laragon\www\ruanglari\resources\views\tools\calculator.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

new_css = """<style>
        #rl-calculator {
            --primary: #1e293b;
            --primary-dark: #0f172a;
            --secondary: #64748b;
            --accent: #1e293b;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --card-bg: #ffffff;
            --panel-bg: #f8fafc;
            --border: #e2e8f0;
            --text: #334155;
            --muted: #64748b;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        #rl-calculator * { box-sizing: border-box; font-family: inherit; }

        #rl-calculator .rlc-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        #rl-calculator .rlc-header { text-align: center; margin-bottom: 2rem; }
        #rl-calculator .rlc-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: .5rem;
            line-height: 1.2;
        }
        #rl-calculator .rlc-header p { margin: 0; color: var(--muted); font-weight: 500; font-size: 1.1rem; }

        #rl-calculator .rlc-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        #rl-calculator .global-controls { display: flex; justify-content: center; margin-bottom: 1.5rem; }
        #rl-calculator .unit-selector { padding: 1.5rem; width: min(640px, 100%); }
        #rl-calculator .unit-selector label { display: block; margin-bottom: .5rem; font-weight: 700; color: var(--text); }
        #rl-calculator .unit-selector select {
            width: 100%;
            height: 48px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0 1rem;
            background: var(--panel-bg);
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease;
        }
        #rl-calculator .unit-selector select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,41,59,0.1); }

        #rl-calculator .tab-navigation {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.25rem 0.25rem 1rem 0.25rem;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        #rl-calculator .tab-navigation::-webkit-scrollbar { display: none; }
        
        #rl-calculator .tab-btn {
            scroll-snap-align: start;
            flex: 0 0 auto;
            height: 44px;
            padding: 0 1.25rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #ffffff;
            color: var(--muted);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        #rl-calculator .tab-btn:hover {
            background: var(--panel-bg);
            color: var(--text);
        }
        #rl-calculator .tab-btn.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(30,41,59,0.15);
        }

        #rl-calculator .tab-content {
            display: none;
            padding: 1.5rem;
            margin-top: 1rem;
            scroll-margin-top: 110px;
        }
        #rl-calculator .tab-content.active { display: block; }

        #rl-calculator .info-note {
            background: #f0fdf4;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--success);
            font-size: 0.95rem;
            color: #166534;
        }

        #rl-calculator .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: end;
        }
        #rl-calculator .form-group { margin-bottom: 1.25rem; }
        #rl-calculator label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 600; 
            color: var(--text); 
            font-size: 0.85rem; 
        }
        #rl-calculator input, #rl-calculator select {
            width: 100%;
            height: 48px;
            padding: 0 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.15s ease;
            background: var(--panel-bg);
            color: var(--text);
            outline: none;
        }
        #rl-calculator input:focus, #rl-calculator select:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 0 3px rgba(30,41,59,0.1); 
            background: #ffffff;
        }
        #rl-calculator input:disabled { opacity: 0.6; cursor: not-allowed; background: #e2e8f0; }

        #rl-calculator .time-inputs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
        #rl-calculator .time-inputs input { text-align: center; }

        #rl-calculator button.rlc-action {
            width: 100%;
            height: 48px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }
        #rl-calculator button.rlc-action:hover { 
            background: var(--primary-dark); 
            box-shadow: 0 4px 12px rgba(30,41,59,0.2); 
            transform: translateY(-1px);
        }

        #rl-calculator .results {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 16px;
            display: none;
            border: 1px solid #e2e8f0;
            scroll-margin-top: 110px;
        }
        #rl-calculator .results.show { display: block; }
        #rl-calculator .result-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            margin-bottom: 0.5rem;
        }
        #rl-calculator .result-grid { display: grid; gap: 0.75rem; }
        #rl-calculator .result-label { font-weight: 600; color: var(--muted); font-size: 0.9rem; }
        #rl-calculator .result-value { font-weight: 800; color: var(--primary); font-size: 1.1rem; }

        #rl-calculator .rlc-export-btn {
            width: 100%;
            display: none;
            margin-top: 1rem;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            height: 44px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: transparent;
            color: #475569;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        #rl-calculator .rlc-export-btn:hover { 
            background: #f1f5f9; 
            border-color: #94a3b8; 
            color: #1e293b;
        }

        #rl-calculator .error {
            color: #b91c1c;
            background: #fef2f2;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1rem;
            border-left: 4px solid var(--error);
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            #rl-calculator .rlc-wrap { padding: 2rem; }
            #rl-calculator .rlc-header h1 { font-size: 2.5rem; }
            #rl-calculator .tab-content { padding: 2rem; }
            #rl-calculator .form-row { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
            #rl-calculator .tab-navigation { flex-wrap: wrap; justify-content: center; }
            #rl-calculator button.rlc-action { max-width: 300px; margin-left: auto; margin-right: auto; display: block; }
        }
        
        @media (min-width: 1024px) {
            #rl-calculator .rlc-wrap { max-width: 1200px; }
        }
    </style>"""

# 1. Replace the entire <style> block
content = re.sub(r'<style>.*?</style>', new_css, content, flags=re.DOTALL)

# 2. Clean up specific dark mode hardcoded inline styles in HTML
replacements = [
    (r'background:\s*rgba\(\s*2,\s*6,\s*23,\s*\.3\s*\)', r'background: #f8fafc'),
    (r'border:\s*1px solid rgba\(\s*148,\s*163,\s*184,\s*\.1\s*\)', r'border: 1px solid #e2e8f0'),
    (r'color:#fff', r'color:#1e293b'),
    (r'color: var\(--muted\)', r'color: #64748b'),
    (r'color:var\(--primary\)', r'color: #1e293b'),
    (r'color:\s*rgba\(\s*226,\s*232,\s*240,\s*\.82\s*\)', r'color: #475569'),
    (r'color:rgba\(\s*204,\s*255,\s*0,\s*\.95\s*\)', r'color: #1e293b'),
    (r'color:#10b981', r'color:#059669'), # slightly darker green for light mode
    (r'color:#ef4444', r'color:#dc2626'),
    (r'background:\s*rgba\(\s*15,\s*23,\s*42,\s*\.9\s*\)', r'background: #ffffff'), # tooltips
    (r'titleColor:\s*\'#fff\'', r"titleColor: '#1e293b'"),
    (r'bodyColor:\s*\'#cbd5e1\'', r"bodyColor: '#475569'"),
    (r"color:\s*'#94a3b8'", r"color: '#64748b'"), # chart ticks
    (r'rgba\(\s*204,\s*255,\s*0,\s*\.8\s*\)', r"'#1e293b'"), # chart line color
    (r'background:\s*linear-gradient[^;]+;', r''), # remove neon gradients inline if any
]

for old, new in replacements:
    content = re.sub(old, new, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("CSS and inline styles updated successfully.")
