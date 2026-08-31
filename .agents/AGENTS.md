# Workspace Rules

- **Do not automatically perform `git commit` or `git push`**: Do not stage, commit, or push code changes to the Git repository unless explicitly requested by the user. Leave all changes unstaged in the workspace for manual review.

# UI/UX & Design Guidelines (Strictly Mandatory)

- **No AI Aesthetic (Anti-AI Clichés)**:
  - Designs must look human-crafted, professional, clean, and production-ready. Avoid generic AI-generated vibes.
  - **No Pill Badges Everywhere**: Do not default to rounded pill shapes (`rounded-full`) for all badges, tags, and small cards. Use clean, subtle rectangular styling (`rounded-sm`, `rounded`, `rounded-md`).
  - **No Neon Glow Effects**: Avoid excessive neon glow shadows (`shadow-neon`, `shadow-[0_0_..._#...]`, `blur`, `drop-shadow`). Keep components solid, crisp, and tactile.
  - **No Repetitive Uppercase + Italic**: Do not overuse `uppercase italic font-black tracking-tight` on every heading, title, or label. Use balanced, natural typographic hierarchy (clean title case or sentence case, bold/semibold without excessive italic shouting).
  - **No Low-Contrast / Excessive `text-slate-500`**: Never spam `text-slate-500` or dark washed-out gray for regular text, descriptions, and labels. Secondary texts and subtitles must remain high-contrast and legible (`text-slate-300`, `text-slate-200`, `text-white/80`).
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

## Border Radius Standard (Mandatory)

All UI components MUST follow this border-radius scale. Do NOT use values outside this list:

| Element             | Class         | Notes                                       |
|---------------------|---------------|---------------------------------------------|
| Buttons             | `rounded-md`  | All buttons, CTAs, form submits              |
| Inputs / Selects    | `rounded-md`  | Text inputs, dropdowns, textareas            |
| Cards / Panels      | `rounded-lg`  | Content cards, stat boxes, filter panels     |
| Modals              | `rounded-lg`  | Modal dialogs, confirmation popups           |
| Badges / Tags       | `rounded`     | Status badges, difficulty labels, tag chips  |
| Tables              | `rounded-lg`  | Table wrapper container only                 |
| Avatars / Photos    | `rounded-full`| Profile photos and avatar circles ONLY       |
| Progress Bars       | `rounded-sm`  | Progress bar track and fill                  |
| Alerts / Toasts     | `rounded-md`  | Notification banners, flash messages         |

**Banned values on non-avatar elements**: `rounded-xl`, `rounded-2xl`, `rounded-3xl`, `rounded-full`

## Button Icon Policy (Mandatory)

- **Text buttons DO NOT get icons** unless the button is a primary "Create/Add" action (then use `+` text, not an SVG icon).
- **Icon-only buttons** are acceptable for compact table actions (edit, delete) but should use small FontAwesome icons (`text-xs`) with no background.
- **Do NOT pair an icon + text** on every button. The text is sufficient.

## Typography Scale (Mandatory)

| Usage               | Class                                   | Do NOT use                                    |
|---------------------|-----------------------------------------|-----------------------------------------------|
| Page Title (h1)     | `text-2xl font-bold text-white`         | `font-black`, `italic`, `tracking-tighter`    |
| Section Title (h2)  | `text-lg font-semibold text-white`      | `font-extrabold`, `font-black`, `tracking-tight` |
| Card Title          | `text-base font-semibold text-white`    | `font-black`                                  |
| Body Text           | `text-sm text-slate-200`               | `text-slate-500`                              |
| Subtitle / Label    | `text-sm text-slate-300`               | `font-mono uppercase tracking-widest`         |
| Caption / Meta      | `text-xs text-slate-400`               | `text-[10px]`, `text-[11px]` (use sparingly)  |
| Monospace Values     | `text-sm font-mono text-white`         | Apply only on numeric/code values, never labels |
