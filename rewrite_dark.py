import re

file_path = r'c:\laragon\www\ruanglari\resources\views\tools\calculator.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

new_css = """<style>
        #rl-calculator {
            --primary: #ccff00;
            --primary-dark: #a3cc00;
            --secondary: #3b82f6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --card-bg: rgba(30,41,59,.72);
            --panel-bg: rgba(2,6,23,.42);
            --border: rgba(148,163,184,.18);
            --text: rgba(226,232,240,.92);
            --muted: rgba(148,163,184,.88);
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        #rl-calculator * { box-sizing: border-box; }

        /* Container Layout adjustments from redesign */
        #rl-calculator .rlc-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        #rl-calculator .rlc-header { text-align: center; margin-bottom: 2rem; }
        #rl-calculator .rlc-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, rgba(204,255,0,.95) 0%, rgba(6,182,212,.95) 50%, rgba(59,130,246,.95) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: .5rem;
            line-height: 1.2;
        }
        #rl-calculator .rlc-header p { margin: 0; color: var(--muted); font-weight: 600; }

        /* Card Layout */
        #rl-calculator .rlc-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 0 0 1px rgba(2,6,23,.45) inset, 0 2px 8px rgba(0,0,0,0.06);
            padding: 1.5rem;
        }

        #rl-calculator .global-controls { display: flex; justify-content: center; margin-bottom: 1.5rem; }
        #rl-calculator .unit-selector { padding: 1.5rem; width: min(640px, 100%); }
        #rl-calculator .unit-selector label { display: block; margin-bottom: .5rem; font-weight: 800; color: var(--text); }
        #rl-calculator .unit-selector select {
            width: 100%;
            height: 48px; /* Redesign input height */
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
            border: 1px solid rgba(148,163,184,.24);
            border-radius: 12px;
            padding: 0 1rem;
            background: var(--panel-bg);
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease;
        }
        #rl-calculator .unit-selector select:focus { border-color: rgba(204,255,0,.6); box-shadow: 0 0 0 3px rgba(204,255,0,.14); }

        /* Tab Navigation Layout */
        #rl-calculator .tab-navigation {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.25rem 0.25rem 1rem 0.25rem;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }
        #rl-calculator .tab-navigation::-webkit-scrollbar { display: none; }
        
        #rl-calculator .tab-btn {
            scroll-snap-align: start;
            flex: 0 0 auto;
            height: 44px;
            padding: 0 1.25rem;
            border: 1px solid #334155; /* slate-700 */
            border-radius: 12px;
            background: #1e293b; /* slate-800 */
            color: #94a3b8; /* slate-400 */
            font-weight: 700;
            font-size: .85rem;
            cursor: pointer;
            transition: all .2s ease;
            white-space: nowrap;
        }
        #rl-calculator .tab-btn:hover {
            background: #334155; /* slate-700 */
            color: #f1f5f9; /* slate-100 */
            border-color: #475569; /* slate-600 */
        }
        #rl-calculator .tab-btn.active {
            background: #ccff00; /* neon */
            color: #0f172a; /* dark */
            border-color: #ccff00;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(204, 255, 0, 0.25);
        }

        #rl-calculator .tab-content {
            display: none;
            padding: 1.5rem;
            margin-top: 1rem;
            scroll-margin-top: 110px;
        }
        #rl-calculator .tab-content.active { display: block; }

        #rl-calculator .info-note {
            background: rgba(2,6,23,.35);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid rgba(204,255,0,.9);
            font-size: .9rem;
            color: var(--text);
        }

        /* Form Layout */
        #rl-calculator .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: end;
        }
        #rl-calculator .form-group { margin-bottom: 1.25rem; }
        #rl-calculator label { display: block; margin-bottom: 6px; font-weight: 800; color: rgba(226,232,240,.88); font-size: .85rem; }
        
        #rl-calculator input, #rl-calculator select {
            width: 100%;
            height: 48px; /* Form sizing */
            padding: 0 1rem;
            border: 1px solid rgba(148,163,184,.22);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            transition: all .15s ease;
            background: var(--panel-bg);
            color: var(--text);
            outline: none;
        }
        #rl-calculator input:focus, #rl-calculator select:focus { border-color: rgba(204,255,0,.6); box-shadow: 0 0 0 3px rgba(204,255,0,.14); }
        #rl-calculator input:disabled { opacity: .65; cursor: not-allowed; }

        #rl-calculator .time-inputs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
        #rl-calculator .time-inputs input { text-align: center; }

        #rl-calculator button.rlc-action {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, rgba(204,255,0,.95) 0%, rgba(6,182,212,.95) 55%, rgba(59,130,246,.95) 100%);
            color: rgba(2,6,23,.92);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 900;
            cursor: pointer;
            transition: all .15s ease;
            margin-top: 0.5rem;
        }
        #rl-calculator button.rlc-action:hover { transform: translateY(-1px); box-shadow: 0 10px 25px rgba(0,0,0,.25); }

        #rl-calculator .results {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: rgba(15,23,42,.45);
            border-radius: 16px;
            display: none;
            border: 1px solid rgba(148,163,184,.18);
            scroll-margin-top: 110px;
        }
        #rl-calculator .results.show { display: block; }
        #rl-calculator .result-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            background: rgba(2,6,23,.4);
            border-radius: 12px;
            border: 1px solid rgba(148,163,184,.14);
            margin-bottom: 0.5rem;
        }
        #rl-calculator .result-grid { display: grid; gap: 0.75rem; }
        #rl-calculator .result-label { font-weight: 700; color: rgba(226,232,240,.82); font-size: .85rem; }
        #rl-calculator .result-value { font-weight: 900; color: rgba(204,255,0,.95); font-size: 1rem; }

        #rl-calculator .rlc-export-btn {
            width: 100%;
            display: none;
            margin-top: 1rem;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            height: 44px;
            border-radius: 12px;
            border: 1px solid rgba(204,255,0,.35);
            background: rgba(204,255,0,.08);
            color: rgba(226,232,240,.92);
            font-weight: 900;
            cursor: pointer;
            transition: all .15s ease;
        }
        #rl-calculator .rlc-export-btn:hover { transform: translateY(-1px); background: rgba(204,255,0,.14); border-color: rgba(204,255,0,.5); }

        #rl-calculator .error {
            color: #fecaca;
            background: rgba(239,68,68,.14);
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1rem;
            border-left: 4px solid var(--error);
            font-size: .9rem;
        }

        /* Desktop and Tablet Breakpoints */
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

content = re.sub(r'<style>.*?</style>', new_css, content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("CSS updated successfully with dark theme and new layout.")
