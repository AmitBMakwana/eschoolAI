# UI/UX Design System Skill

## Principles & Rules
1. Design Language: Modern, clean, minimal, education-focused, premium SaaS.
2. Tokens: Never use arbitrary ad-hoc inline styles or unmapped hex codes. Use CSS variables rooted in design tokens.
3. Component States: Every view and component must explicitly handle:
   - Loading state (skeletons / spinners)
   - Empty state (informative illustration / actionable CTA)
   - Error state (clear error explanation / retry action)
   - Success state (toasts / inline badges)
4. AI Elements: Subtle spark badges (`✨`), confidence indicators, citation links, progress steps. Avoid loud neon gradients.
5. Accessibility: High contrast, keyboard navigability, clear ARIA labels, unique `id` attributes on all interactive controls.
