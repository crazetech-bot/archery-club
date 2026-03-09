# `claude.md`
### **Archery Training & Development System — Development Specification for Claude**

This document defines the architecture, conventions, and instructions for Claude to follow when generating code, migrations, models, controllers, Vue components, and documentation for the **Archery Training & Development System** — a single-tenant, module-based Laravel + Vue application installed individually for each club or academy.

---

# 1. **Primary Responsibilities**

Claude is the primary development engine for this system.

Claude must always prioritize:
- Correctness
- Consistency
- Modularity
- Safety
- Modern UI/UX
- TailwindCSS best practices
- Zero hallucination

---

# 2. **Architecture Rules (Mandatory)**

## 2.1 Single-Tenant Only

Claude must **never** generate multi-tenant code or reference tenancy packages.

## 2.2 Module-Based Structure

All code belongs to one of these modules:

1. Core Accounts & Roles
2. Archers & Coaches
3. Training Sessions
4. Live Scoring
5. Equipment Setups
6. Lanes & Bookings
7. Competitions
8. Reports Engine

Claude must **never** mix modules.

## 2.3 Directory Structure

All files must be placed in the correct Laravel/Vue structure.

---

# 3. **Database Schema (Authoritative)**

Claude must follow the exact schema definitions provided in the specification.

**Never invent fields or omit required fields.**

---

# 4. **Coding Standards**

Claude must follow:

- PSR-12
- Eloquent models
- Form Requests
- Services for business logic
- API Resources
- Thin controllers
- Vue 3 `<script setup>`
- TailwindCSS for all styling

---

# 5. **UI/UX Requirements (Mandatory)**

Everything generated must look:

- Professional
- Modern
- Clean and minimalistic
- Mobile-first
- TailwindCSS-styled
- Consistent with a premium SaaS aesthetic
- Dark-mode compatible

## 5.1 TailwindCSS Rules

All Vue components, layouts, and pages must:

- Use TailwindCSS utility classes
- Use responsive classes (`sm:`, `md:`, `lg:`)
- Use consistent spacing (`p-4`, `gap-6`, `mt-8`)
- Use rounded corners (`rounded-2xl`)
- Use subtle shadows (`shadow-sm`, `shadow-md`)
- Use neutral palettes (slate/gray/stone) with accent colors (indigo/emerald)
- Use flexbox and grid appropriately
- Use semantic HTML

## 5.2 Layout Requirements

Generated layouts must include:

- A clean top navigation or sidebar
- Proper spacing and padding
- A responsive container
- A modern, uncluttered look
- Tailwind utility classes only
- No inline CSS
- No external CSS frameworks

## 5.3 Component Requirements

All components must:

- Be reusable
- Be accessible
- Use Tailwind for styling
- Follow a consistent design language

---

# 6. **Output Rules (Critical)**

## 6.1 Full Files Only

Claude must always output **complete files**, never snippets.

## 6.2 One File Per Code Block

Each file must be in its own fenced code block.

## 6.3 No Guessing

If unclear, ask for clarification.

---

# 7. **Development Flow (Mandatory Order)**

Claude must follow this order when building features:

1. Migrations
2. Models
3. Routes
4. Controllers
5. Form Requests
6. Services
7. Resources
8. Vue Pages & Components
9. Seeders
10. Tests

Claude must **never** generate code out of order unless explicitly instructed.

---

# 8. **Live Scoring Logic (Authoritative)**

Claude must implement live scoring using this exact 7-step flow:

1. Start session
2. Select archer
3. Create end
4. Record arrows (1–6)
5. Calculate end score
6. Generate session summary
7. Export PDF (future module)

---

# 9. **Reports Engine**

Reports must use:

- Eloquent queries
- Aggregations
- Vue charts
- Optional PDF export

---

# 10. **What Claude Must Never Do**

Claude must never:

- Mix module logic
- Mix database contexts
- Generate multi-tenant code
- Generate partial files
- Invent fields or tables
- Place logic in controllers
- Output placeholder text
- Output "…" or incomplete code
- Generate code not requested
- Change the architecture

---

# 11. **Default Behavior**

Unless the user specifies otherwise, Claude must:

- Generate production-ready code
- Follow the module structure
- Follow the database schema
- Follow the development order
- Use TailwindCSS
- Produce professional, modern UI
- Return full files only
