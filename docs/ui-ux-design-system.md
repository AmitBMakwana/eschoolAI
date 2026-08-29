# UI/UX Design System Specification

## 1. Design Philosophy
- **Identity:** Modern, Clean, Minimalist, Education-focused, Premium SaaS.
- **Visuals:** High-contrast clean typography, subtle borders (`#E2E8F0`), refined shadows, uncluttered whitespace.
- **AI Accents:** Subtle purple/indigo shimmer (`#6366F1`), sparkle badges (`✨`), and progress step cards rather than chaotic neon gradients.

## 2. Design Tokens
- **Colors:**
  - `Primary`: `#4F46E5` (Indigo-600)
  - `Primary Hover`: `#4338CA`
  - `Secondary`: `#0EA5E9` (Sky-500)
  - `Success`: `#10B981` (Emerald-500)
  - `Warning`: `#F59E0B` (Amber-500)
  - `Danger`: `#EF4444` (Rose-500)
  - `Background`: `#F8FAFC`
  - `Surface`: `#FFFFFF`
  - `Text Main`: `#0F172A` (Slate-900)
  - `Text Muted`: `#64748B` (Slate-500)
- **Typography:** Inter, Plus Jakarta Sans, or Outfit (Google Fonts).
- **Spacing Scale:** `4px`, `8px`, `12px`, `16px`, `24px`, `32px`, `48px`.
- **Border Radius:** `sm: 4px`, `md: 8px`, `lg: 12px`, `full: 9999px`.

## 3. Mandatory Component States
Every screen and component must include:
1. **Loading State:** Shimmering skeleton placeholders matching the exact card/table layout.
2. **Empty State:** Clean vector iconography with a clear title and an actionable call-to-action button.
3. **Error State:** Human-readable error message with a retry trigger.
4. **Success State:** Toast alerts and inline affirmative badges.
