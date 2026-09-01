---
name: ux-review
description: Audits UI/UX against user flows, accessibility, usability frictions, and anti-AI compliance before final delivery.
---

# UX REVIEW SKILL

## PURPOSE

Analyze existing interfaces and identify UX problems before release.

This skill helps agents:

- review existing UI
- detect usability issues
- find unnecessary elements
- improve user experience
- prioritize fixes

The goal is not to redesign everything.

The goal is:

"Make the product easier, clearer, and more useful."

---

# REVIEW PRINCIPLE

Never judge design only by appearance.

A beautiful interface can still fail if:

- users cannot understand it
- users cannot complete tasks
- information is unclear
- interactions are confusing


Always review:

1. User goal
2. User flow
3. Visual hierarchy
4. Interaction
5. Accessibility
6. Performance perception

---

# REVIEW PROCESS

Follow this order:


UNDERSTAND
↓
OBSERVE
↓
IDENTIFY PROBLEMS
↓
PRIORITIZE FIXES
↓
RECOMMEND IMPROVEMENTS


Do not immediately redesign.

---

# PHASE 1 — PRODUCT UNDERSTANDING

Before reviewing, identify:


## Product Type

Example:

- SaaS dashboard
- Mobile app
- Landing page
- Marketplace
- Internal tool
- Portfolio


## Primary User

Define:

- Who uses it?
- What is their skill level?
- What device do they use?


## Main User Goal

Ask:

"What is the most important action users need to complete?"

Examples:

- buy product
- submit form
- track performance
- read information
- manage data

---

# PHASE 2 — USER FLOW REVIEW

Analyze the journey.

Check:

## Entry Point

Can users immediately understand:

- where they are?
- what the product does?
- what they should do?


## Navigation

Check:

- Is navigation predictable?
- Are important pages easy to find?
- Are menus overloaded?


## Task Completion

Ask:

"How many steps are needed to complete the main action?"


Reduce unnecessary steps.

---

# PHASE 3 — VISUAL HIERARCHY REVIEW

Check:

## First Impression

Within 3 seconds:

Can users understand:

- product purpose?
- primary action?
- important information?


If not:

Improve:

- headline
- spacing
- contrast
- layout


---

# INFORMATION HIERARCHY

Review:

## Priority Levels

Level 1:

Most important information

Level 2:

Supporting information

Level 3:

Secondary details


Avoid:

Everything looking equally important.

---

# PHASE 4 — AI DESIGN DETECTION

Identify generic AI patterns.

Flag:

## Visual Problems

☐ Excessive gradients

☐ Glassmorphism without purpose

☐ Too many rounded cards

☐ Random icons

☐ Decorative illustrations

☐ Empty bento grids

☐ Neon colors

☐ Excessive shadows

☐ Template layouts


Recommendation:

Remove before adding.

---

# PHASE 5 — COMPONENT REVIEW


Review each component:


## Buttons

Check:

- Is the purpose clear?
- Is hierarchy obvious?
- Is the primary action visible?


Avoid:

Multiple primary buttons competing.


---

## Forms

Check:

- Are labels clear?
- Are errors understandable?
- Is validation helpful?


Avoid:

Placeholder-only forms.

---

## Cards

Ask:

"Does this information need a card?"


Remove cards when:

- simple text is enough
- grouping has no meaning

---

## Tables

Check:

- readability
- sorting
- filtering
- scanning speed


Avoid:

Showing too much data at once.

---

# PHASE 6 — ACCESSIBILITY REVIEW


Check:

## Contrast

Ensure:

- text readable
- buttons visible
- states understandable


Avoid:

Light gray text on white backgrounds.


---

## Typography

Check:

- font size
- line height
- readability


---

## Interaction

Check:

- touch target size
- keyboard navigation
- focus state


Minimum touch target:

48px

---

# PHASE 7 — MOBILE REVIEW


Review mobile experience.

Check:

## Layout

- Does content fit?
- Are buttons reachable?
- Is scrolling reasonable?


## Interaction

- Are actions easy with one hand?
- Are important controls accessible?


---

# PHASE 8 — STATE REVIEW


Every application needs:


## Loading

Question:

"What does user see while waiting?"


Avoid:

Blank screen.


---

## Empty State

Explain:

- why it is empty
- what user can do


---

## Error State

Provide:

- understandable message
- recovery action


---

## Success Feedback

Confirm:

- action completed
- next step

---

# PRIORITY SYSTEM


Classify findings:


## Critical

Blocks users from completing tasks.

Example:

- broken flow
- unclear primary action
- inaccessible feature


Fix immediately.


---

## High

Creates frustration.

Example:

- confusing navigation
- poor hierarchy
- unnecessary steps


Fix before release.


---

## Medium

Improves quality.

Example:

- spacing
- typography
- visual consistency


---

## Low

Minor polish.

Example:

- small animation
- decorative improvement

---

# REVIEW OUTPUT FORMAT


When reviewing UI, always provide:


## 1. Overall Assessment

Example:

"The interface is functional but feels like a generic dashboard."


## 2. Main Problems

List:

- issue
- impact
- recommendation


Format:

Problem:

Impact:

Fix:


## 3. Priority Fix List


P0:
Critical changes


P1:
Important improvements


P2:
Polish


## 4. Final Recommendation

Explain:

What should be changed first.

---

# FINAL RULE

Do not say:

"This design is bad."

Say:

"This design creates friction because..."


The purpose of UX review is improvement, not criticism.

---

# GOLDEN PRINCIPLE

Good UX removes confusion.

Great UX makes the correct action feel obvious.