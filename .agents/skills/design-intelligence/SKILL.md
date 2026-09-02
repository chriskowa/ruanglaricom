---
name: design-intelligence
description: Sets visual direction, brand strategy, color palette, typography, and anti-AI layout principles before coding. Incorporates avoid-ai-design rules to eliminate generic AI frontend slop.
---

# DESIGN INTELLIGENCE & ANTI-AI DESIGN SKILL

> *"Craft interfaces that look human-designed, purposeful, and distinctive — completely free of AI-generated slop."*

## PURPOSE
Guide the agent to make intentional visual and architectural design decisions before writing frontend code. It eliminates generic AI tropes (purple gradients, default Inter font everywhere, uncustomized shadcn components, liquid glassmorphism, floating orb blurs) and delivers tailored, production-ready interfaces.

================================================

# PHASE 1 — PRODUCT CONTEXT & INTENTIONALITY

Before writing any HTML/CSS/Tailwind:

## 1. Context Analysis
Answer these 4 fundamental questions:
1. **What is the product domain?** (e.g. Athletic endurance sports, property management, fintech, developer tool)
2. **Who is the end user and under what conditions?** (e.g. A running coach on a mobile browser outdoors in sunlight vs an admin on a dual-monitor desktop)
3. **What is the primary action on this screen?** (e.g. Verify registration, filter participants, submit payment)
4. **What visual tone should it project?** (e.g. Athletic high-performance, utilitarian clarity, editorial elegance, technical precision)

## 2. Anti-AI Rule: Never Design on Autopilot
- Do NOT generate a generic "modern SaaS dashboard with 3 cards and a purple header".
- Every design decision (color, font, layout, density) must directly serve the product domain.

================================================

# PHASE 2 — COLOR STRATEGY (AVOID-AI PALETTE)

## 1. The Anti-AI Color Policy
AI generators default to a predictable formula: dark background + purple-to-blue gradient + pink/cyan accents + neon glow. **Ban this formula.**

### Strictly Prohibited AI Color Patterns:
- ❌ Purple-to-indigo or purple-to-pink gradient buttons (`from-purple-600 via-indigo-600 to-pink-500`)
- ❌ Neon glowing outlines (`shadow-[0_0_20px_rgba(168,85,247,0.5)]`)
- ❌ Rainbow status badges without semantic meaning (using green, yellow, blue, red simultaneously without hierarchy)
- ❌ Washed-out pastel text on dark background (`text-slate-500` on black)

### Human-Crafted Color Principles:
- **Maximum 2–3 Brand Colors**: One primary identity color, one supporting neutral scale, one accent for primary CTA only.
- **High Contrast Ratio**: Minimum 4.5:1 (WCAG AA) for all body text (`text-white`, `text-slate-200`, `text-slate-900`).
- **Solid, Crisp Backgrounds**: Use rich, solid dark tones (`#080A0D`, `#12161D`) or clean crisp light tones (`#F8FAFC`, `#FFFFFF`), never muddy translucent layers.

================================================

# PHASE 3 — TYPOGRAPHY WITH CHARACTER

AI generators almost exclusively default to `Inter`, `Geist`, or `Space Grotesk` with `uppercase tracking-widest` for every label.

## 1. Domain-Specific Typography
Choose font pairings that reflect the actual product:
- **Athletic / Sports**: Condensed, strong, energetic titles (*Oswald*, *Bebas Neue*, *Barlow Condensed*) + Clean legible body (*Inter*, *Plus Jakarta Sans*) + Monospace for telemetry/times (*JetBrains Mono*, *Roboto Mono*).
- **Fintech / Corporate**: Authoritative, stable sans/serif (*Plus Jakarta Sans*, *Instrument Sans*, *Source Serif*).
- **Editorial / Premium**: High-contrast serif headlines (*Playfair Display*, *Cinzel*) + Utilitarian body.
- **Data & Telemetry**: Always format timestamps, BIB numbers, currency, and IDs in **Monospace** font for instant visual scanning.

## 2. Typographic Hierarchy Scale
- **H1 (Page Title)**: `text-2xl font-bold text-white tracking-tight` (Avoid over-stylized `font-black italic tracking-tighter`).
- **H2 (Section)**: `text-lg font-semibold text-white`.
- **Body Text**: `text-sm text-slate-200` with comfortable line height (`leading-relaxed`).
- **Data / Metrics**: `text-sm font-mono text-white`.
- **Meta / Helpers**: `text-xs text-slate-400`.

================================================

# PHASE 4 — LAYOUT COMPOSITION (NO AI-SLOP TEMPLATES)

## 1. Ban AI Template Layouts
- ❌ **No "3 Identical Feature Cards"**: Stop putting 3 identical columns with a centered icon, bold title, and 1 generic sentence.
- ❌ **No Empty Bento Grids**: Do not slice the screen into 6 rounded boxes unless each box holds dense, meaningful information.
- ❌ **No Fake Terminal Mockups**: Do not put a fake code editor or mock terminal window on non-developer products.
- ❌ **No Floating Blurred Blobs / Orbs**: Remove `backdrop-blur` background radial blobs (`blur-3xl bg-purple-500/20`) that distract from content.

## 2. Human-Crafted Layout Architecture
- **Asymmetrical & Data-Driven**: Group related controls together, give primary workflows prominent space.
- **Ample Container Padding**: Generous spacing (`p-5`, `p-6`), ample gaps (`gap-4`, `gap-6`).
- **Solid Opaque Panels**: Use 100% solid surfaces (`bg-slate-800 border border-slate-700`). No unreadable translucent glassmorphism (`bg-white/5 backdrop-blur-md`).

================================================

# PHASE 5 — COMPONENT & ICON POLICY

## 1. Border Radius Standards (Anti-Pill Rule)
Dilarang mengubah semua elemen menjadi bentuk pil lonjong (`rounded-full`). Gunakan standar border-radius kotak presisi:
- **Tombol & Input Form**: `rounded-md`
- **Kartu, Panels, & Modal**: `rounded-lg`
- **Badges & Tags**: `rounded`
- **Progress Bar Track**: `rounded-sm`
- **Avatar / Foto Profil Bulat**: `rounded-full` (Hanya untuk foto profil/avatar)

## 2. Functional Icon Policy
- **No Emojis Anywhere**: Strict zero-tolerance policy for emojis (🏃, ⚡, 🚀, ✨, dll.) in UI, buttons, badges, and alerts.
- **No Sparkles (✨ / Fa-Sparkles)**: Remove all AI sparkle decorations.
- **No Icons on Plain Text Buttons**: Buttons like "Simpan", "Batal", "Tutup" do not need icons. Use icons only for fast-scan table actions or primary "+" create buttons.

================================================

# PHASE 6 — AVOID-AI-DESIGN TRANSFORMATION CATALOG

| AI Slop Pattern (Before) | Human-Crafted Design (After) |
|---|---|
| `bg-gradient-to-r from-purple-600 to-pink-500 rounded-full shadow-lg` | `bg-emerald-600 hover:bg-emerald-500 text-white rounded-md font-semibold text-sm` |
| `backdrop-blur-xl bg-white/5 border border-white/10 rounded-2xl` | `bg-slate-900 border border-slate-800 rounded-lg p-6 shadow-sm` |
| Lucide icon on every heading, label, and list item | Typography scale & negative space to establish hierarchy |
| `font-sans` default Inter everywhere with all-caps tracking-widest | Intentional font pair (e.g. Oswald headings + Inter body + Mono numbers) |
| Floating moving arrows & continuous bounce animations | Quiet, instantaneous hover transitions (`transition duration-150`) |
| Placeholder marketing fluff ("Supercharge your workflow") | Actionable, clear data & user feedback |