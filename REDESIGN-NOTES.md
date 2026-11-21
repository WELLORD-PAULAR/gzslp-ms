# Graphic Zone UI redesign

This update changes presentation only for the landing page, shared customer-facing layout and pages, administrator workspace, and employee workspace.

## Brand direction
- Cyan: primary interactive and navigation color
- Magenta: secondary accent
- Yellow: call-to-action and highlight color
- Charcoal / white: neutral surfaces and contrast

## Build the frontend
From the project root, install dependencies and compile the updated Tailwind/Vite assets:

```bash
npm install
npm run build
```

For local development, use the existing Laravel/Vite workflow (`npm run dev`).

## Scope
No controllers, models, migrations, routes, database schema, or business workflows were intentionally changed. The administrator and employee interfaces share the staff workspace layout, with administrator-only navigation remaining permission-gated.

The uploaded logo is installed at `public/images/graphic-zone-logo.png`.
