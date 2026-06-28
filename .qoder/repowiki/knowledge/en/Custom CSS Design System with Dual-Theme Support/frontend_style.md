## Overview

This Laravel application uses a **pure custom CSS** approach (no Tailwind, Bootstrap, or other utility-first framework) built on top of **CSS custom properties (design tokens)** and organized into reusable component classes. The styling is compiled via **Vite** and delivered through Blade layouts.

## Core Technology Stack

- **Build Tool**: Vite (`vite.config.js`) with `laravel-vite-plugin`
- **CSS Methodology**: Custom CSS with design tokens (CSS variables), BEM-inspired naming conventions
- **JavaScript Interactivity**: Alpine.js (loaded via CDN at `3.x.x`)
- **Icon Library**: Font Awesome 6.4.0 (CDN)
- **Typography**: Google Fonts — Inter (primary UI font), Playfair Display (decorative headings in dark theme)
- **No CSS Preprocessor**: Plain CSS only; no Sass/SCSS/Less detected
- **No PostCSS Plugins**: Postcss exists as a transitive dependency but no `postcss.config.js` file present

## Design Token System

All visual values are centralized in CSS custom properties declared at `:root` in `resources/css/app.css`:

```css
:root {
    --primary: #FF3366;
    --primary-hover: #E62E5C;
    --secondary: #6C63FF;
    --accent: #00D2D3;
    --background: #F4F7FE;
    --surface: #FFFFFF;
    --text-main: #2B3674;
    --text-muted: #A3AED0;
    --shadow-soft: 0 10px 20px -5px rgba(112, 144, 176, 0.2);
    --shadow-strong: 0 20px 40px -10px rgba(112, 144, 176, 0.4);
    --shadow-colored: 0 15px 25px -5px rgba(255, 51, 102, 0.4);
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-xl: 32px;
    --radius-full: 9999px;
    --transition-fluid: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
```

These tokens drive consistent spacing, color, elevation, and motion across all components.

## Theme Architecture

The application supports **dual-theme switching** (light/dark) controlled by Alpine.js stores and persisted to `localStorage`:

- **Light theme** (default): Uses the base `:root` token values above
- **Dark culinary theme**: Overrides tokens within `.home-dashboard` and page-specific selectors (`.dashboard-page`, `.menu-page`, `.cart-page`, etc.)
- **Theme toggle mechanism**: An inline script in `layouts/app.blade.php` reads `kantin-theme` from localStorage and applies `theme-light` or `theme-dark` classes to `<html>`
- **Motion preference**: Respects `prefers-reduced-motion` media query and a user-controlled `kantin-reduce-motion` flag; when active, disables animations via `html.reduce-motion * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }`

Dark theme overrides are scoped under selectors like:
```css
.home-dashboard {
    --primary: #ff704f;
    --background: #0e0e0e;
    --surface: #171717;
    --text-main: #f7efe8;
    --text-muted: #c6bbb1;
}
```

And further refined with `html.theme-light` prefix rules for light-mode exceptions within dark-themed pages.

## Component Library

The CSS file (~6100 lines) defines a comprehensive set of reusable component classes:

### Layout Components
- `.container` — max-width 1200px centered wrapper
- `.grid`, `.grid-cols-2/3/4` — CSS Grid utilities
- `.navbar` — fixed glassmorphism header with backdrop blur
- `.admin-sidebar` / `.admin-content` — fixed sidebar layout for admin dashboard

### UI Components
- `.card` — elevated surface with hover lift effect
- `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-outline` — gradient buttons with shadow and scale transitions
- `.input` — styled form inputs with focus ring
- `.badge` — pill-shaped notification counters
- `.status-badge` variants (`.status-pending`, `.status-dibuat`, `.status-diantar`, `.status-sampai`, `.status-selesai`) — color-coded order status indicators
- `.table-container` / `table` — styled data tables with row hover

### Page-Specific Sections
- `.hero` / `.home-hero` — full-viewport hero sections with gradient overlays and decorative radial backgrounds
- `.menu-card` / `.menu-showcase-card` — product cards with image, price, points badge
- `.chat-widget` — floating chat window with pulse animation
- `.accordion-card` — expandable order detail panels with bounce animation
- `.invoice-sheet` — print-optimized invoice layout with watermark
- `.kitchen-panel` — slide-in side navigation for dark-themed pages
- `.service-panel` — feature cards with icon and description

### Animation System
Keyframe animations defined globally:
- `fadeInUp` — entrance animation with opacity + translateY
- `float` — continuous vertical bobbing
- `pulse-soft` — expanding ring shadow effect
- `marqueeSlide` / `chefMarquee` — horizontal scrolling text/image carousels
- `accordionBounce` — spring-like panel expansion

Animation delays provided via `.delay-100`, `.delay-200`, `.delay-300` utility classes.

## Responsive Strategy

Mobile breakpoints handled via `@media (max-width: 768px)` queries that:
- Collapse multi-column grids to single column
- Hide admin sidebar on mobile
- Stack flex containers vertically
- Reduce font sizes and spacing
- Adjust invoice watermark positioning

Print styles defined via `@media print` with:
- A4 page size, 8mm margins
- Hidden navigation and non-essential elements (`.no-print`)
- Optimized invoice typography and layout
- Forced color rendering for watermarks

## File Organization

All styling lives in a **single monolithic CSS file** (`resources/css/app.css`). There is no modular CSS architecture (no separate files per component, no SCSS partials). The file is structured sequentially:
1. Design tokens (`:root`)
2. Global resets and typography
3. Animations
4. Component definitions (buttons, cards, forms)
5. Layout sections (navbar, hero, footer)
6. Page-specific styles (dashboard, menu, auth, orders, invoice)
7. Theme overrides (dark mode, light mode exceptions)
8. Responsive breakpoints
9. Print styles

## Developer Conventions

1. **Always use design tokens**: Reference `--primary`, `--radius-lg`, etc. instead of hardcoding colors or sizes
2. **Component class naming**: Use descriptive, lowercase-with-dashes names (e.g., `.menu-showcase-card`, `.status-badge`)
3. **State modifiers**: Prefix with context (e.g., `.is-open` on accordion cards, `.active` on nav items)
4. **Utility classes**: Limited set available (`.text-center`, `.mt-1` through `.mt-4`, `.mb-1` through `.mb-4`, `.flex`, `.items-center`, `.justify-between`, `.gap-1`, `.gap-2`)
5. **Alpine.js for interactivity**: Use `x-data`, `x-show`, `x-text`, and `$store` for dynamic UI state (cart count, theme toggle, dropdown menus)
6. **Blade template integration**: Styles are loaded via `@vite(['resources/css/app.css', 'resources/js/app.js'])` in layout head
7. **No inline styles except for dynamic values**: Avoid inline `style` attributes; use CSS classes. Exception: conditional icon colors via `style="color: var(--primary)"`
8. **Accessibility**: Respect `prefers-reduced-motion`; ensure sufficient color contrast in both themes
