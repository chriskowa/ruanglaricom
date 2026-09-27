---
name: web-design-guidelines
description: Review UI code for Web Interface Guidelines compliance. Use when asked to "review my UI", "check accessibility", "audit design", "review UX", or "check my site against best practices".
metadata:
  author: vercel-labs
  version: "1.0.0"
  argument-hint: <file-or-pattern>
---

# WEB INTERFACE GUIDELINES (WEB-DESIGN-GUIDELINES)

> *"Quality is not an accident; it is the accumulation of hundreds of small, deliberate details executed consistently."*

Based on the official **Vercel Web Interface Guidelines** (`vercel-labs/web-interface-guidelines`), this skill audits frontend UI code (HTML, Blade, React, Vue, CSS/Tailwind) for compliance with modern accessibility, UX, animation, typography, and performance standards.

---

## AUDIT WORKFLOW

When auditing a file, component, or pattern:
1. Read the target UI files.
2. Check against the technical rules below.
3. Output findings in a concise, actionable `file:line` format with high signal-to-noise.
4. Provide concrete, drop-in replacement fixes.

---

## 1. ACCESSIBILITY (WCAG & SEMANTICS)

- **Semantic HTML over ARIA**: Use `<button>`, `<a>`, `<label>`, `<nav>`, `<main>`, `<article>`, `<header>`, `<footer>`, and `<table>` natively before reaching for ARIA attributes.
- **Buttons vs Links**:
  - `<button type="button|submit">` for actions, modals, and toggles (never `<div onclick>` or `<a href="#">`).
  - `<a href="...">` for URL navigation and routing.
- **Icon-Only Buttons**: Any button or link containing only an icon MUST have an explicit `aria-label="Deskripsi Aksi"`.
- **Decorative Graphics & SVGs**: Non-informational icons and decorative illustrations MUST have `aria-hidden="true"`. Informational graphics need descriptive text or `<title>`.
- **Form Labels**: Every form control (`<input>`, `<select>`, `<textarea>`) MUST have an associated `<label for="id">` or `aria-label`.
- **Heading Hierarchy**: Only one `<h1>` per page. Heading hierarchy must strictly follow `<h1>` → `<h2>` → `<h3>` without skipping levels.
- **Heading Anchors**: Headings with anchor targets (`id="..."`) MUST include `scroll-margin-top: {header-height}` (e.g., `scroll-mt-20`) so sticky navbars don't obscure the title when jumped to.
- **Dynamic Updates**: Asynchronous updates (toast notifications, real-time counters, inline validation) require `aria-live="polite"`.
- **Color Contrast**: Body copy must maintain a minimum contrast ratio of 4.5:1 against its background (WCAG AA). Avoid low-contrast text (e.g., avoid `text-slate-500` on dark canvas; use `text-slate-200` or `text-slate-300`).

---

## 2. FOCUS STATES & KEYBOARD NAVIGATION

- **Visible Focus**: All interactive elements (buttons, links, inputs, selects, tabs) MUST have a clearly visible focus state (e.g., `focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:outline-none`).
- **No Bare Outline Removal**: Never use `outline-none` or `outline: none` without providing an active focus ring replacement.
- **`:focus-visible` over `:focus`**: Always use `:focus-visible` so mouse clicks don't show awkward persistent focus rings while keyboard tab navigation remains fully accessible.
- **Group Focus**: Use `:focus-within` on compound input groups or search bars to show a single cohesive focus state.
- **Covering Elements**: Sticky headers, floating CTAs, and cookie banners must never cover currently focused interactive elements.

---

## 3. FORMS & INPUT CONTROLS

- **Clickable Labels**: Clicking a `<label>` must focus the input control (`for="id"` pairing or control nested inside `<label>`).
- **Checkboxes & Radios**: The label text and input MUST share a single generous hit target (minimum 44x44px tap zone on mobile). No dead gaps between box and text.
- **Input Types & Modes**:
  - Use semantic types: `type="email"`, `type="tel"`, `type="number"`, `type="url"`.
  - Specify `inputmode="numeric"` for digits-only inputs (OTP, year, postal code, phone).
- **Autocomplete & Name**: Form fields must have meaningful `name` and appropriate `autocomplete` attributes (e.g., `autocomplete="name"`, `autocomplete="email"`, `autocomplete="tel"`). Use `autocomplete="off"` on non-auth search fields to prevent unwanted password manager prompts.
- **Spellcheck Hygiene**: Add `spellcheck="false"` on emails, user codes, tokens, usernames, and URLs.
- **No Paste Blocking**: Never intercept `onpaste` with `preventDefault()`. Users must be able to paste credentials, VDOT numbers, or codes.
- **Submit Feedback**:
  - The submit button remains enabled until the network request starts.
  - Show a spinner / loading indicator during the request.
  - Disable button during active flight to prevent double-submits, but preserve button dimensions to avoid layout shifting.
- **Inline Validation**: Errors should appear directly beneath the invalid field with `role="alert"`. When submit fails, focus the first invalid input automatically.
- **Placeholders**: Placeholders must end with an ellipsis `…` and show an exemplary pattern, not act as a replacement for labels.

---

## 4. ANIMATION & MOTION SAFETY

- **Respect Reduced Motion**: Always honor `prefers-reduced-motion: reduce`. Provide a static fallback or disable parallax/smooth scrolls (`motion-reduce:transition-none`, `motion-reduce:animate-none`).
- **Never `transition: all`**: Never use generic `transition: all`. Explicitly list animated properties (e.g., `transition: color, background-color, border-color, transform`).
- **Compositor-Only Animation**: Only animate `transform` and `opacity`. Never animate `width`, `height`, `margin`, `padding`, or `top/left` as they trigger expensive browser reflow and repaint.
- **Interruptible Animations**: Ensure animations cancel cleanly and respond immediately to user interactions mid-flight.
- **Decorative Motion Loops**: Autoplaying background loops lasting longer than 5 seconds must pause under reduced-motion and should offer a pause/stop toggle.

---

## 5. TYPOGRAPHY & DATA FORMATTING

- **Tabular Numbers**: Any column, table, metric strip, countdown, stopwatch, pace calculator, or telemetry display MUST use `font-variant-numeric: tabular-nums` or `font-numeric` so characters maintain uniform width and do not jump.
- **Smart Punctuation**:
  - Use true ellipsis `…` (`&hellip;`), not three periods `...`.
  - Use directional curly quotes `“` `”` instead of typewriter quotes `"` in prose.
- **Non-Breaking Spaces**: Use `&nbsp;` between values and units to prevent awkward wraps (e.g., `5&nbsp;KM`, `21.1&nbsp;KM`, `Rp&nbsp;150.000`, `180&nbsp;SPM`).
- **Heading Line Balance**: Use `text-wrap: balance` or `text-wrap: pretty` on headings and hero subheads to avoid single-word hanging lines (*widows*).
- **Loading Strings**: Status messages indicating in-progress operations should end with `…` (e.g., `"Memuat…"`).

---

## 6. CONTENT HANDLING & DEFENSIVE UI

- **Text Truncation**: Card titles, user-generated descriptions, and coach names must be resilient against overflow using `truncate`, `line-clamp-2`, or `break-words`.
- **Flexbox Min-Width**: Flex child containers that hold truncated text MUST have `min-w-0` to allow CSS ellipsis truncation to activate.
- **Graceful Empty States**: Never render a blank screen, broken table, or empty card grid when an array or search result is empty. Provide an explicit, helpful Empty State with recovery actions (e.g., "Reset Filter").
- **Variable Length Resilience**: Test layouts with minimal data (short names) and extreme data (3-line titles, long multi-word cities, large currency numbers).

---

## 7. IMAGES & MEDIA PERFORMANCE

- **Prevent Layout Shifts (CLS)**: Every `<img>` MUST have locked dimensions (`width` and `height` attributes) or a constrained CSS aspect ratio (e.g., `aspect-[16/10]`, `aspect-[3/2]`).
- **Lazy Loading**: Images below the initial viewport fold MUST have `loading="lazy"`.
- **High-Priority Hero**: Critical above-the-fold hero images or key banners SHOULD specify `fetchpriority="high"` and omit `loading="lazy"`.
- **Image Fallbacks**: User avatars and program covers must provide a defensive `onerror="this.onerror=null; this.src='...'"` fallback so missing images never break the UI card layout.

---

## 8. PERFORMANCE & DOM EFFICIENCY

- **Virtualization for Large Data**: Lists containing more than 50 dynamic items should utilize virtual scrolling or server-side pagination.
- **Batch DOM Reads/Writes**: Avoid interleaving layout reads (`getBoundingClientRect`, `offsetHeight`, `scrollTop`) with DOM mutations to prevent layout thrashing.
- **Off-Canvas Rendering**: Use `content-visibility: auto` on heavy sections below the fold to allow the browser to skip layout computations until scrolled into view.

---

## AUDIT OUTPUT FORMAT

When running this audit, present findings as:

```markdown
### Web Interface Guidelines Audit Report

- [FAIL] `resources/views/programs/index.blade.php:207`: Icon-only button missing `aria-label`
  → Fix: Add `aria-label="Pilih Program Pelatih"`
- [FAIL] `resources/views/programs/index.blade.php:237`: Input missing visible focus replacement for `outline-none`
  → Fix: Add `focus-visible:ring-2 focus-visible:ring-orange-500`
- [PASS] Heading hierarchy: Verified single `<h1>` with sequential `<h2>` sections
- [PASS] Tabular numbers: Verified telemetry metrics use `tabular-nums`
```
