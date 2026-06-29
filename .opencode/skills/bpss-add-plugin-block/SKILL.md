---
name: bpss-add-plugin-block
description: Adds or extends a BePlus Advanced Reviews Gutenberg block under blocks/ using block.json, TypeScript, esbuild, render.php. Use when creating blocks, editing BlockRegistry, block.json, render.php, index.tsx, edit.tsx, view.ts, or build scripts for beplus-advanced-reviews.
---

# BePlus Advanced Reviews — add or change a plugin block

## Before you edit

- Read [`AGENTS.md`](../../../AGENTS.md) § **Gutenberg blocks** and [`Document Plugin.md`](../../../Document%20Plugin.md) § **Gutenberg Block — advanced-review**.
- Read `docs/advanced-review-block.md` when building or changing the primary `advanced-review` block.
- Read `docs/review-filter-ux.md` and `docs/review-media.md` for block behavior, accessibility, image handling, and filter UX.
- Registration: `src/Blocks/BlockRegistry.php` — auto-discovers `blocks/*/block.json`.

## Scaffold

1. Create `blocks/{slug}/` with `block.json`, `index.tsx`, `edit.tsx`, `view.ts`, `render.php`, `style.css`.
2. Set `block.json`:
   - `name`: `beplus-advanced-reviews/{slug}`
   - `textdomain`: `beplus-advanced-reviews`
   - `category`: `beplus-advanced-reviews`
   - `render`: `file:./render.php`
3. Register the block category in `Plugin::register_block_category()` if needed.

## Implement — advanced review block

1. **Attributes:** `showDistribution`, `showFilterBar`, `showSubmitForm`, `showImages`, `showAvatar`, `reviewsPerLoad`, `enableLazyLoad`.
2. **render.php:** wrap with `get_block_wrapper_attributes()`; render review list, star distribution chart, filter controls, sort controls, submit form shell, and image placeholders; escape all output.
3. **edit.tsx:** provide Inspector controls for review display and filtering options.
4. **view.ts:** hydrate client-side filter interactions, image lightbox behavior, load more pagination, star distribution chart, and paste-to-upload support.
5. **REST:** use `beplus-advanced-reviews/v1/reviews` and `beplus-advanced-reviews/v1/reviews/distribution` for live data and submission flows; do not embed ad-hoc SQL in the block.

## Styling

- Class prefix: `beplus-advanced-reviews__*`
- Loading: `beplus-advanced-reviews--loading` → `beplus-advanced-reviews--ready`
- Scope styles to the block wrapper — avoid global resets

## Build and verify

1. `npm run build` from plugin root.
2. Block inserter: block appears under **BePlus Advanced Reviews** category.
3. Front-end: filters work, images open correctly, star distribution renders, review submission states are visible, and layout remains stable.

## Reference

| Source | Use for |
|--------|---------|
| `blocks/advanced-review/block.json` | Primary block metadata, attributes, render callback |
| `src/Blocks/BlockRegistry.php` | Auto-discovery |
| `src/Core/Placement.php` | Display mode logic |
| `docs/advanced-review-block.md` | Block spec, UX, and review flow |
| `docs/review-filter-ux.md` | Filter UX, DOM contract, accessibility |
| `docs/review-media.md` | Image previews, uploads, paste, and lightbox behavior |

## Checklist

- [ ] `block.json` uses `beplus-advanced-reviews/` name prefix and textdomain.
- [ ] `render.php` escaped; no raw user input in output.
- [ ] REST used for live review data — not ad-hoc SQL in render.
- [ ] `npm run build` run; no hand-edits to generated files.
- [ ] A11y: labels, filter controls, focus-visible, reduced motion, live regions.
