# Personal Finance Manager — DESIGN.md

## Design System

### Typography
- **UI:** Plus Jakarta Sans (`font-sans`)
- **Display:** Fraunces (`font-display`) for page titles and stat values
- **Scale:** `text-2xs` (labels) → `text-sm` (body) → `text-lg` (subheads) → `text-2xl`+ (display)
- **Monetary values:** `font-display`, semibold; emerald (income), red (expense), brand (balance)

### Color Palette
- **Ink:** warm neutrals `ink-50` … `ink-950` (text, surfaces)
- **Brand:** sage/teal `brand-50` … `brand-950` (accent, active nav, progress)
- **Surface:** off-white cards on `surface-50` page background
- **Semantic:** emerald-600 income, red-500 expense, amber-500 budget warning

### Components (CSS utilities in `app.css`)
- `.card` — white panel, soft shadow
- `.card-interactive` — hover lift for clickable cards
- `.btn-primary` — ink-900 fill
- `.btn-secondary`, `.btn-ghost`, `.btn-danger`
- `.input`, `.label`
- `.stat`, `.stat-value`, `.stat-label`

### Spatial
- **Page max-width:** `max-w-7xl` (authenticated), `max-w-4xl` for focused flows
- **Card padding:** `p-6` standard, `p-8` auth
- **Section rhythm:** `space-y-6` / `space-y-8`

### Interaction
- Solid nav (no backdrop blur)
- Month navigator with « Aujourd'hui » when not current month
- Type toggle segmented control (no emoji)
- Category shown as color dot + name (`CategoryBadge`)

### Empty States
- `EmptyState` component: icon slot, title, description, optional CTA

### Responsive
- Mobile-first; nav collapses to hamburger below `lg`
- Guest auth: single column mobile, split panel desktop (solid ink panel, no blur)
