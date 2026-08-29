# Frontend Development Workflow

## Objective
Standardized workflow for implementing responsive, accessible, token-compliant user interface components and wizard views.

## Steps
1. Consult UI/UX Design System tokens (`design-tokens.json` / CSS variables).
2. Build reusable UI components (table, form inputs, modal, skeleton, toast, AI badge).
3. Implement view with all 4 states: Loading, Empty, Error, Success.
4. Add unique `id` and `data-testid` attributes to interactive elements.
5. Connect to `/api/v1/` endpoints with graceful error handling.
6. Verify layout responsiveness across Desktop, Tablet, and Mobile viewports.
