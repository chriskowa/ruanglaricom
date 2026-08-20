# Workspace Rules

- **Do not automatically perform `git commit` or `git push`**: Do not stage, commit, or push code changes to the Git repository unless explicitly requested by the user. Leave all changes unstaged in the workspace for manual review.

# UI/UX & Design Guidelines (Strictly Mandatory)

- **No AI Aesthetic**: Designs must look human-crafted, professional, clean, and production-ready. Avoid generic AI-generated vibes.
- **NO Emojis Anywhere**: Never use emojis (no 🏃‍♂️, 🏔️, 💨, ⚡, 🚀, ⏱️, etc.) in UI templates, alerts, popups, buttons, or placeholder texts. Use clean, professional text or standard FontAwesome icons sparingly.
- **Minimalist & Purposeful Icons**: Do not over-clutter interfaces with icons on every single label or button. Keep icon usage minimal and purposeful.
- **Solid Colors Only (No Gradients, No Transparent/Blurry Overlays)**:
  - Use solid, crisp background colors (e.g., solid `#0c121e`, `#111724`, `#090D16`, `#070B12`).
  - Do not use background gradients (`bg-gradient-to-...`) or semi-transparent washed-out backgrounds (`bg-slate-900/40`, `backdrop-blur-sm`).
  - Modals and cards must be 100% solid and opaque with clear borders (`border-slate-700` or `border-slate-800`).
- **High Contrast & Legible Typography**:
  - Text must have strong contrast and never blend into dark backgrounds (`text-white`, `text-slate-200`, `text-slate-300`).
  - Placeholder texts must be clearly readable and sharp (`placeholder:text-slate-400` or `placeholder-slate-400`, never dark/washed out `placeholder-slate-600/700`).
  - Typography must remain strictly consistent with the existing website design system (Inter / clean sans-serif hierarchy).
