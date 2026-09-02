---
name: ux-review
description: Audits UI/UX against user flows, accessibility, usability frictions, and anti-AI compliance before final delivery.
---

# UX REVIEW & ANTI-AI AUDIT SKILL

> *"Ensure every screen is frictionless, accessible, and 100% free of AI-generated design slop before shipping."*

## PURPOSE
Conduct a rigorous audit of frontend interfaces before release. It detects usability frictions, enforces accessibility standards, and executes the `avoid-ai-design` quality gate to remove generic template patterns.

================================================

# AUDIT WORKFLOW

```
[1] CONTEXT AUDIT (Does the interface match user goals & product domain?)
 └── [2] AI-SLOP DETECTION (Scan and flag all AI design clichés)
      └── [3] FRICTION & USABILITY (Analyze navigation, form fields, and click depth)
           └── [4] ACCESSIBILITY & CONTRAST (Verify WCAG AA, readability, touch targets)
                └── [5] REFACTORING & POLISH (Apply concrete human-crafted fixes)
```

================================================

# PHASE 1 — THE AVOID-AI-DESIGN AUDIT CHECKLIST

Scan the interface and immediately flag any of the following 10 AI Slop Telltales:

### 1. Color & Gradient Slop
- [ ] Are there purple-blue-pink gradient buttons or hero headers? → **Replace with solid brand color.**
- [ ] Are there neon glowing borders or neon text? → **Replace with clean border tokens.**
- [ ] Are there low-contrast washed-out text colors (`text-slate-500` on dark)? → **Fix to `text-slate-200` or `text-white`.**

### 2. Layout & Composition Slop
- [ ] Is there an uninspired "3 identical cards in a row" layout? → **Redesign to match true content hierarchy.**
- [ ] Are there empty or under-utilized bento grids? → **Consolidate into dense, meaningful panels.**
- [ ] Are there floating background blur orbs (`blur-3xl bg-purple-500/20`)? → **Delete all background orbs.**
- [ ] Is there a fake terminal / code editor on a non-developer tool? → **Replace with real product telemetry/UI.**

### 3. Typography & Copy Slop
- [ ] Is default `Inter` used everywhere without deliberate font choice? → **Adopt domain-specific typography.**
- [ ] Are numbers and telemetries in standard proportional font? → **Switch to `font-mono`.**
- [ ] Are there AI marketing clichés ("Unlock the power of...", "Not just X, it's Y")? → **Rewrite to clear, direct human copy.**
- [ ] Are there excessive em dashes (`—`)? → **Replace with clean periods and commas.**

### 4. Component & Element Slop
- [ ] Are non-avatar elements styled as pills (`rounded-full`)? → **Enforce `rounded-md` for buttons/inputs and `rounded-lg` for cards.**
- [ ] Are there emojis (🏃, ⚡, 🚀, ✨, dll.) anywhere in the UI? → **Remove all emojis completely.**
- [ ] Are there sparkle icons (✨ / `fa-sparkles`)? → **Remove sparkles and replace with real metrics.**
- [ ] Is there liquid glassmorphism (`backdrop-blur` with semi-transparent background)? → **Make cards 100% solid and opaque.**
- [ ] Are there decorative Lucide/FontAwesome icons before every heading and text button? → **Remove purely decorative icons.**

================================================

# PHASE 2 — USABILITY & FRICTION AUDIT

## 1. Interaction & Flow
- **Click Depth**: Can the user achieve the main action in the fewest possible clicks?
- **Form Friction**: Are all required inputs actually visible? (No hidden required fields that cause validation errors).
- **Destructive Actions**: Do deletion / cancel buttons have clear confirmation states?
- **Feedback States**: Does every interactive element give immediate visual feedback on click/submit?

## 2. UI State Coverage (The 4 Essential States)
Verify that the interface explicitly handles:
1. **Loading State**: Subtle skeleton loader (no jarring layout shifts).
2. **Empty State**: Clear, friendly message with an actionable next step when data is empty.
3. **Error State**: Non-blocking alert banners with understandable guidance.
4. **Success State**: Clear confirmation feedback upon completion.

================================================

# PHASE 3 — ACCESSIBILITY & CONTRAST (WCAG AA)

- **Color Contrast**: Body text must achieve at least **4.5:1** contrast ratio against its background.
- **Touch Target Size**: Interactive mobile buttons/links must have a minimum clickable area of **44x44px** or `py-2 px-3`.
- **Keyboard Navigation**: Form inputs, buttons, and modals must have visible focus rings (`focus:ring-2 focus:ring-slate-400 focus:outline-none`).
- **Semantic Structure**: Exactly one `<h1>` per page, followed by logical `<h2>`, `<h3>` hierarchy.

================================================

# PHASE 4 — REFACTORING PROTOCOL

When issues are detected:
1. **Do Not Rewrite Blindly**: First identify what is working well.
2. **Strip the Fluff First**: Remove fake decorative borders, unneeded icons, emojis, and glassmorphism.
3. **Solidify Surfaces**: Convert translucent containers to opaque, well-padded cards (`p-5`, `p-6`).
4. **Sharpen Typography & Spacing**: Establish strong visual hierarchy with distinct font weights and monospace numbers.
5. **Verify Mobile Responsiveness**: Ensure full functionality and comfortable tap targets across mobile viewports.