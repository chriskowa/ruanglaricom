# Workspace Rules

- **Do not automatically perform `git commit` or `git push`**: Do not stage, commit, or push code changes to the Git repository unless explicitly requested by the user. Leave all changes unstaged in the workspace for manual review.

# UI/UX & Design Guidelines (Strictly Mandatory)

- **No AI Aesthetic**: Designs must look human-crafted, professional, clean, and production-ready. Avoid generic AI-generated vibes.
- **NO Emojis Anywhere**: Never use emojis (no 🏃‍♂️, 🏔️, 💨, ⚡, 🚀, ⏱️, etc.) in UI templates, alerts, popups, buttons, or placeholder texts. Use clean, professional text or standard FontAwesome icons sparingly.
- **Minimalist & Purposeful Icons**: Do not over-clutter interfaces with icons on every single label, button, or heading. Keep icon usage minimal, subtle, and purposeful. A text button or label does not need an icon unless strictly necessary.
- **Solid Colors Only (No Broken Arbitrary Hex, No Gradients, No Blur/Transparency)**:
  - **Always use standard, valid Tailwind solid classes** (e.g., `bg-slate-950`, `bg-slate-900`, `bg-slate-800`, `bg-black`, `bg-dark`, `bg-card`).
  - **Never use invalid arbitrary syntax** such as `bg-['#090D16']` or unrecognized custom arbitrary hex strings that fail to compile or render blank/transparent.
  - **Do not use background gradients** (`bg-gradient-to-...`) or semi-transparent/washed-out layers (`bg-slate-900/40`, `backdrop-blur-sm`, `bg-white/5`).
  - **Modals, cards, and dropdowns must be 100% solid and opaque** with clear, crisp borders (`border-slate-800` or `border-slate-700`).
- **Generous Spacing & Whitespace (Anti-Crowded / Breathing Room)**:
  - **Do not crowd the UI**: Interfaces must not feel cramped or stuffed with tightly packed text, labels, and icons.
  - **Ample Container Padding**: Use generous padding on cards and modals (`p-5`, `p-6` or larger), never cramped padding (`p-2`, `p-3`) for main containers.
  - **Comfortable Gaps & Separation**: Use `space-y-4` to `space-y-6` between form/content sections, and `gap-3.5` to `gap-6` between grid cards and interactive elements.
  - **Breathing Room for Typography**: Ensure comfortable margin between titles, descriptions, and inputs (`mb-1.5`, `mt-1`, `leading-relaxed`). Let empty space breathe naturally.
- **High Contrast & Legible Typography**:
  - Text must have strong contrast and never blend into dark backgrounds (`text-white`, `text-slate-200`, `text-slate-300`).
  - Placeholder texts must be clearly readable and sharp (`placeholder:text-slate-400` or `placeholder-slate-400`, never dark/washed out `placeholder-slate-600/700`).
  - Typography must remain strictly consistent with the existing website design system (Inter / clean sans-serif hierarchy).
