# Design QA — login style selector

## Visual truth

- Glass source: `../qa-assets/glass-source.png`
- Glass implementation: `../qa-assets/glass-implementation.png`
- Glass comparison: `../qa-assets/glass-comparison.png`
- Portal source: `../qa-assets/portal-source.png`
- Portal implementation: `../qa-assets/portal-implementation.png`
- Portal comparison: `../qa-assets/portal-comparison.png`

All desktop captures use the same 1487 × 1058 viewport and the same OAuth-first login state. The source and implementation screenshots are both 1487 × 1058 pixels.

## Implementation scope

- Added a persisted `login_style` setting with `classic`, `glass`, and `portal` values.
- Added the selector to the GLPI plugin configuration page.
- Preserved the existing image, logo, color, text, alignment, SSO, and native GLPI form controls.
- Added responsive layouts for the centered glass card and the full-height side portal.
- Added a configurable presentation heading for the new layouts without changing authentication behavior.

The existing shared stylesheet is 57,370 bytes / 1,733 lines after the change. The login presets are isolated by `welcome-anonymous`, `eso-login-custom`, and the selected style class to avoid affecting authenticated GLPI screens.

## Checks

- Desktop full-view comparison: passed for both styles.
- Focused login panel comparison: passed; card position, translucency, hierarchy, and side-panel proportions follow the selected visual targets while retaining configurable colors and text.
- OAuth pane: passed.
- Switch to native GLPI login form: passed; labels, placeholders, submit text, and password recovery text remain configurable.
- Mobile at 390 × 844: passed for both styles; no horizontal overflow, portal hero is hidden, and the card is centered.
- Browser console warnings/errors: none.
- PHP lint and smoke tests: passed.
- JavaScript syntax check: passed.

## Iteration history

1. First capture showed an oversized light card, duplicate title hierarchy, and a portal panel shorter than the reference.
2. Moved the configurable heading into the composition, removed nested-card styling, tightened the glass card, and extended/narrowed the portal panel.
3. Re-captured both styles, compared each source and implementation side by side, and repeated desktop/mobile interaction checks.

## Final result

passed
