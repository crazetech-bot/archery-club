# `claude.md`
### **Archery Performance OS — Development Specification for Claude**

This document defines the architecture, conventions, and instructions for Claude to follow when generating code, migrations, models, controllers, Vue components, and documentation for the **Archery Performance OS** SaaS platform.

---

# 1. **Project Overview**

We are building a **multi-tenant SaaS** archery training and management system with:

- Archer module
- Coach module
- Club management module
- Live scoring system
- Analytics & reports
- Equipment tracking
- Lane booking
- Competition management

The system uses **separate database per tenant** (Phase 2 architecture).

---

# 2. **Tech Stack**

### **Backend**
- Laravel (latest version)
- Stancl Tenancy (separate DB per tenant)
- Spatie Permissions (role-based access)
- Laravel Jetstream or Breeze (auth)
- Laravel Cashier (Stripe billing)
- Redis (queues, cache)
- FFmpeg (video processing)

### **Frontend**
- Vue.js 3
- Inertia.js or API-based SPA
- TailwindCSS
- Optional: Livewire for interactive components

### **Infrastructure**
- Central database (global)
- One database per tenant (club)
- S3-compatible storage (Wasabi, DO Spaces)
- Supervisor for queue workers

---

# 3. **Multi-Tenant Architecture Rules**

### **Central Database Contains:**
- Tenants
- Users (global)
- Tenant-user mapping
- Subscriptions
- Global configs

### **Tenant Database Contains:**
- Archers
- Coaches
- Training sessions
- Live scoring tables
- Equipment setups
- Memberships
- Lane booking
- Competitions
- Reports data

### **Routing**
- Central app: `https://mynetdns.info`
- Tenants: `https://{tenant}.mynetdns.info`

### **Claude must always:**
- Generate migrations for **central DB** separately from **tenant DB**
- Use Stancl Tenancy conventions
- Use tenant-aware models and controllers
- Use `tenant()` helper when needed

---

# 4. **Database Schema Definitions**

Claude must use these schemas when generating migrations.

---

## 4.1 **Central Database Tables**

### **tenants**
```
id
name
slug
db_name
db_host
db_username
db_password (encrypted)
plan
status
created_at
updated_at
```

### **users**
```
id
name
email
password
is_super_admin
created_at
updated_at
```

### **tenant_user**
```
id
tenant_id
user_id
role (club_admin, coach, archer)
created_at
updated_at
```

### **subscriptions**
```
id
tenant_id
provider
provider_customer_id
provider_subscription_id
plan
status
renews_at
created_at
updated_at
```

---

## 4.2 **Tenant Database Tables**

### **archers**
```
id
user_id (nullable)
name
gender
date_of_birth
category
notes
timestamps
```

### **coaches**
```
id
user_id
name
level
notes
timestamps
```

### **training_sessions**
```
id
archer_id
coach_id (nullable)
date
type
round_type
distance
notes
timestamps
```

### **live_sessions**
```
id
training_session_id
status (active/completed)
started_at
ended_at (nullable)
timestamps
```

### **live_ends**
```
id
live_session_id
end_number
total_score
x_count
ten_count
tag
notes
timestamps
```

### **live_arrows**
```
id
live_end_id
arrow_number
score
position_x (nullable)
position_y (nullable)
timestamps
```

### **equipment_setups**
```
id
archer_id
bow_type
tiller
brace_height
limb_info
string_info
arrow_info
notes
timestamps
```

### **competitions**
```
id
name
location
date
level
timestamps
```

### **competition_results**
```
id
competition_id
archer_id
category
score
rank
notes
timestamps
```

### **lanes**
```
id
name
description
timestamps
```

### **lane_bookings**
```
id
lane_id
archer_id (nullable)
group_id (nullable)
start_time
end_time
purpose
timestamps
```

---

# 5. **Role System**

Claude must use **Spatie Permissions** inside each tenant DB.

### Roles:
- `club_admin`
- `coach`
- `archer`

### Rules:
- Only club_admin can manage members, lanes, bookings
- Coach can view/manage archers assigned to them
- Archer can only view their own data

---

# 6. **API & Route Structure**

Claude must generate routes grouped by:

### **Central App**
- `/admin/*`
- `/tenants/*`
- `/billing/*`

### **Tenant App**
- `/archer/*`
- `/coach/*`
- `/admin/*` (club admin)
- `/live/*`
- `/reports/*`

Use middleware:
- `auth`
- `tenant`
- `role:coach`
- `role:archer`
- `role:club_admin`

---

# 7. **Frontend Rules**

Claude must generate:

### **Vue Components**
- Live scoring UI
- Coach live monitor
- Archer dashboard
- Club admin dashboard
- Lane booking calendar
- Reports charts (Chart.js or ECharts)

### **Styling**
- TailwindCSS
- Clean, minimal, mobile-first

---

# 8. **Live Scoring Logic**

Claude must follow this logic:

1. Archer starts a live session
2. System creates `live_session`
3. Archer enters ends
4. Each end contains arrows
5. System calculates:
   - Running total
   - Average
   - X count
   - 10 count
6. Coach can monitor in real time
7. Session can be exported as PDF

---

# 9. **Reports Engine**

Claude must implement:

### Archer Reports
- Score progression
- Live session summary
- Grouping heatmap (if coordinates exist)

### Coach Reports
- Archer improvement ranking
- Technique issue frequency

### Club Reports
- Lane usage heatmap
- Membership activity
- Event profitability

---

# 10. **Coding Style Rules**

Claude must follow:

- PSR-12
- Use Eloquent relationships
- Use Resource classes for API responses
- Use Form Request validation
- Use Services for business logic
- Use Repositories if needed
- Use Events for live scoring updates

---

# 11. **How Claude Should Respond**

When asked to generate code:

- Provide **full files**, not snippets
- Include imports, namespaces, and comments
- Ensure migrations match schema
- Ensure models include relationships
- Ensure controllers include validation
- Ensure Vue components are complete and functional

When asked to generate architecture:

- Provide diagrams in text
- Provide folder structures
- Provide step-by-step setup instructions

---

# 12. **What Claude Should Never Do**

- Never mix central DB and tenant DB tables
- Never generate code without tenancy awareness
- Never create partial migrations
- Never create incomplete Vue components
- Never assume roles incorrectly

---

# 13. **Primary Development Flow**

Claude should follow this order when building features:

1. Central DB migrations
2. Tenant DB migrations
3. Models + relationships
4. Routes
5. Controllers
6. Services
7. Vue components
8. Seeders
9. Tests

---

# 14. **Ready Commands for Claude**

You can now ask Claude things like:

- "Generate the central DB migrations."
- "Generate the tenant DB migrations."
- "Create the Live Scoring Vue component."
- "Create the Coach Live Monitor page."
- "Create the Archer Score Progression report controller."
- "Generate the full folder structure for this SaaS."

Claude will follow this document as the project blueprint.
