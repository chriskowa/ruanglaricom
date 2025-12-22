## Navigation & App Shell
- Replace legacy header with a unified dark neon app shell: global search, notification bell, chat quick-link, user avatar menu.
- Add responsive sidebar with clear grouping: Dashboard, Calendar, Programs, Marketplace, Community, Messages, Notifications, Profile.
- Implement sticky header, keyboard shortcuts ("/" for search, "g c" for calendar, "g m" for messages).

## Profile (Edit Profile)
- Convert profile page into a modular form with sections: Identity, Contact, City, Preferences, Privacy.
- Use drawer/modal for quick edits, inline validation, image crop for avatar (WebP pipeline already used).
- Provide preview card and "Save" with optimistic UI feedback.

## Runner Calendar
- Two-mode layout: Month/Week with smooth toggle.
- Color-coded session types (Easy/Tempo/Threshold/Interval/Long); session chips display pace tags.
- Quick add via "+" on a date; edit via modal; drag-and-drop to reschedule (progressive enhancement).
- Right panel shows "This Week" summary: volume, compliance, upcoming key workout.

## Feed (Community)
- Card redesign with media header, metrics row (likes/comments), compact composer at top.
- Inline actions: like/unlike, comment expand, share link.
- Lazy-load images, skeletons for loading, permalink view.

## Notifications (Nav Head & Page)
- Bell dropdown: grouped by Today/This Week; icons per type (comment, follow, enrollment, system).
- Bulk actions: Mark all read, filter by type.
- Full notifications page with pagination and read states.

## Chat
- Two-pane layout: conversations list (left) + messages (right).
- Message composer with attachments (images), emoji picker, read ticks, typing indicator.
- Conversation search and pinning.
- Mobile: slide-over for conversation list; bottom composer always accessible.

## User Lists (Coach & Runner)
- Grid cards with avatar, name, tags, location, badges; CTA: View Profile, Follow.
- Filters: role, city, specialties, pagination/infinite scroll.
- Consistent card hover highlighting and accessible focus states.

## Design System & Components
- Tailwind utilities via `layouts.pacerhub`; standard glass cards, neon accents, font `Inter`.
- Shared partials: `layouts/components/nav.blade.php`, `layouts/components/sidebar.blade.php`, `layouts/components/notification-dropdown.blade.php`.
- Vue (already used) for interactive parts: calendar modals, feed composer, chat; Alpine for simple toggles.

## Accessibility & Performance
- Keyboard navigation for lists and modals; ARIA roles; focus management.
- Image lazy-loading; icon SVG sprites; minimal JS on non-interactive pages.

## Implementation Path (Files)
- Update `resources/views/layouts/pacerhub.blade.php` to include new header/sidebar partials.
- Replace legacy views:
  - `resources/views/runner/dashboard.blade.php` (already modernized; refine widgets)
  - `resources/views/coach/dashboard.blade.php` (already modernized; connect stats)
  - `resources/views/runner/calendar.blade.php` (layout, modals, summary panel)
  - `resources/views/feed/*.blade.php` (cards & composer)
  - `resources/views/notifications/*.blade.php` (dropdown + page)
  - `resources/views/chat/*.blade.php` (two-pane)
  - `resources/views/users/index.blade.php` (filters, cards)
  - `resources/views/profile/*.blade.php` (drawer/modal)

## Validation
- Cross-browser test; verify routes and controllers unchanged.
- Check unread counts and state updates (notifications/chat) via existing APIs.
- Lighthouse pass: performance, accessibility, best practices.

Jika Anda setuju, saya akan mulai dari App Shell (header/sidebar), lalu terapkan ke halaman Calendar, Feed, Chat, Notifications, dan User Lists secara bertahap agar konsisten.