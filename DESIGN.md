# Personal Finance Manager — DESIGN.md

## Design System

### Typography
- **Font family:** System UI (Inter via Tailwind)
- **Scale:** text-xs through text-4xl
- **Headings:** font-semibold, leading-tight
- **Body:** text-sm text-gray-600, leading-relaxed
- **Monetary values:** font-semibold, colored by type (emerald income / red expense / blue balance)

### Color Palette
- **Primary:** Indigo-600 (#4F46E5) — actions, links, navigation
- **Income:** Emerald-500 (#10B981) — positive amounts
- **Expense:** Red-500 (#EF4444) — negative amounts
- **Balance:** Blue-600 (#2563EB) — net position
- **Surface:** White cards on Gray-100 background
- **Text:** Gray-900 primary, Gray-500 secondary, Gray-400 tertiary
- **Budget warnings:** Amber-500 at 75%+, Red-500 at 100%+

### Spatial
- **Page max-width:** 7xl (80rem)
- **Card padding:** p-6
- **Card gap:** gap-4 grid
- **Card radius:** rounded-xl (12px)
- **Card shadow:** shadow-sm with border border-gray-100
- **Section spacing:** space-y-6

### Interaction
- **Buttons:** rounded-lg, text-sm font-medium, hover states
- **Links:** text-indigo-600 hover:text-indigo-800
- **Form inputs:** rounded-lg, border-gray-300, focus:ring-indigo-500
- **Transitions:** transition-all duration-150 ease-in-out

### Responsive
- **Mobile-first:** single column on small screens
- **Tablet:** 2-column grids at md: breakpoint
- **Desktop:** multi-column at lg: breakpoint

### Empty States
- Icon (3rem) + title (text-lg) + description (text-sm text-gray-400)
- CTA button to take first action
