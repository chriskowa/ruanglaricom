---
name: frontend-quality
description: Ensures clean, scalable, accessible, mobile-first responsive code architecture and comprehensive UI states.
---

# FRONTEND QUALITY SKILL

## PURPOSE

Ensure every frontend implementation is:

- clean
- maintainable
- scalable
- responsive
- accessible
- production-ready

This skill focuses on code quality after the design direction has been established.

---

# 01. COMPONENT ARCHITECTURE

Always create reusable components.

Avoid:

- duplicated UI code
- massive single-page components
- repeated markup

Prefer:

- logical component separation
- clear naming
- reusable patterns


Example:

Bad:


Dashboard.vue
├── Header
├── Card
├── Table
├── Modal
└── Form


Better:


components/

Header.vue

StatCard.vue

DataTable.vue

Modal.vue

FormInput.vue


---

# 02. CODE STRUCTURE

Prioritize:

- readability
- consistency
- maintainability

Avoid:

- unnecessary complexity
- unused code
- duplicated logic
- excessive abstraction


Every function and component should have a clear responsibility.

---

# 03. RESPONSIVE DESIGN

Every interface must work on:

- mobile
- tablet
- desktop


Check:

Mobile first:

- layout stacking
- text readability
- button size
- navigation usability


Minimum touch target:

48px

---

# 04. ACCESSIBILITY

Follow accessibility principles.

Ensure:

## Typography

- readable font size
- sufficient contrast
- proper hierarchy


## Interaction

- keyboard accessible
- clear focus states
- understandable labels


## Color

Never rely only on color.

Example:

Bad:

Red = error

Good:

Red + error message + icon/state

---

# 05. UI STATES

Every interactive component must consider:

## Loading State

Use:

- skeleton loader
- progress indicator


Avoid:

blank screen


---

## Empty State

Explain:

- what happened
- what user can do next


---

## Error State

Provide:

- clear message
- recovery action


---

## Success State

Provide:

- confirmation
- feedback


---

# 06. PERFORMANCE

Prioritize:

- fast loading
- optimized assets
- efficient rendering


Avoid:

- unnecessary animations
- huge images
- unused dependencies


---

# 07. CSS QUALITY

Avoid:

- random styling
- duplicated classes
- excessive utility combinations


Maintain:

- design tokens
- consistent spacing
- reusable styles


---

# 08. STATE MANAGEMENT

Use appropriate state management.

Avoid:

- unnecessary global state
- duplicated state
- unclear data flow


Prefer:

- local state when possible
- centralized state only when needed

---

# 09. FORM QUALITY

Forms must have:

- clear labels
- validation
- error messages
- loading feedback


Avoid:

- unclear inputs
- placeholder-only labels
- confusing errors

---

# 10. DATA DISPLAY

For tables, dashboards, and reports:

Prioritize:

- hierarchy
- readability
- scanning speed


Avoid:

- information overload
- unnecessary decoration


Use:

- sorting
- filtering
- grouping

when needed.

---

# 11. ANIMATION POLICY

Animation must support usability.

Allowed:

- transition feedback
- loading animation
- state change


Avoid:

- decorative motion
- excessive hover effects
- continuous animation

---

# 12. CLEAN IMPLEMENTATION CHECKLIST

Before final delivery:

## Code

☐ Components are reusable

☐ No unused code

☐ Naming is clear

☐ Logic is separated from presentation


## UI

☐ Responsive

☐ Accessible

☐ Fast loading

☐ Clear hierarchy


## UX

☐ Loading state exists

☐ Empty state exists

☐ Error handling exists

☐ User feedback exists


---

# FINAL PRINCIPLE

Good frontend is not the most complex code.

Good frontend is:

- easy to understand
- easy to maintain
- pleasant to use
- reliable in real conditions.