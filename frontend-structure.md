# `frontend-structure.md`
## **Archery Performance OS — Frontend Architecture Specification**

This document defines the **frontend structure, conventions, component hierarchy, and UI architecture** for the Archery Performance OS SaaS platform.
Claude must follow this structure when generating Vue components, pages, layouts, and UI logic.

---

# 1. **Frontend Stack**

### **Frameworks & Tools**
- Vue.js 3 (Composition API)
- Inertia.js (preferred) or REST API SPA
- TailwindCSS
- Heroicons (icons)
- Chart.js or ECharts (charts)
- Axios (API requests)
- Laravel Vite (bundling)

---

# 2. **Folder Structure**

Claude must generate frontend code using this structure:

```
resources/
  js/
    App.vue
    app.js
    Pages/
      Auth/
      Dashboard/
      Archer/
      Coach/
      Admin/
      Live/
      Reports/
      Settings/
    Components/
      UI/
      Layouts/
      Forms/
      Charts/
      LiveScoring/
      Tables/
    Composables/
    Stores/
    Utils/
  css/
    app.css
```

---

# 3. **Pages Directory (Inertia Pages)**

Each page corresponds to a route and uses a layout.

### **3.1 Auth Pages**
```
Pages/Auth/Login.vue
Pages/Auth/Register.vue
Pages/Auth/ForgotPassword.vue
Pages/Auth/ResetPassword.vue
```

### **3.2 Dashboard Pages**
```
Pages/Dashboard/ArcherDashboard.vue
Pages/Dashboard/CoachDashboard.vue
Pages/Dashboard/AdminDashboard.vue
```

### **3.3 Archer Pages**
```
Pages/Archer/TrainingSessions.vue
Pages/Archer/TrainingSessionView.vue
Pages/Archer/Equipment.vue
Pages/Archer/Competitions.vue
Pages/Archer/Profile.vue
```

### **3.4 Coach Pages**
```
Pages/Coach/ArcherList.vue
Pages/Coach/ArcherView.vue
Pages/Coach/TrainingPlans.vue
Pages/Coach/LiveMonitor.vue
Pages/Coach/Reports.vue
```

### **3.5 Admin (Club) Pages**
```
Pages/Admin/Members.vue
Pages/Admin/Coaches.vue
Pages/Admin/Lanes.vue
Pages/Admin/LaneBookings.vue
Pages/Admin/Inventory.vue
Pages/Admin/Finance.vue
Pages/Admin/Settings.vue
```

### **3.6 Live Scoring Pages**
```
Pages/Live/StartSession.vue
Pages/Live/ActiveSession.vue
Pages/Live/EndSummary.vue
```

### **3.7 Reports Pages**
```
Pages/Reports/ArcherScoreProgression.vue
Pages/Reports/LiveSessionSummary.vue
Pages/Reports/LaneUsage.vue
Pages/Reports/ClubPerformance.vue
```

---

# 4. **Components Directory**

Reusable UI components.

### **4.1 UI Components**
```
Components/UI/Button.vue
Components/UI/Card.vue
Components/UI/Modal.vue
Components/UI/Badge.vue
Components/UI/Alert.vue
Components/UI/Dropdown.vue
Components/UI/Tabs.vue
```

### **4.2 Layouts**
```
Components/Layouts/AppLayout.vue
Components/Layouts/AuthLayout.vue
Components/Layouts/CoachLayout.vue
Components/Layouts/AdminLayout.vue
```

### **4.3 Forms**
```
Components/Forms/Input.vue
Components/Forms/Select.vue
Components/Forms/Textarea.vue
Components/Forms/DatePicker.vue
Components/Forms/NumberInput.vue
```

### **4.4 Tables**
```
Components/Tables/DataTable.vue
Components/Tables/SortableTable.vue
Components/Tables/PaginatedTable.vue
```

### **4.5 Charts**
```
Components/Charts/LineChart.vue
Components/Charts/BarChart.vue
Components/Charts/PieChart.vue
Components/Charts/Heatmap.vue
```

### **4.6 Live Scoring Components**
```
Components/LiveScoring/ScorePad.vue
Components/LiveScoring/EndInput.vue
Components/LiveScoring/ArrowInput.vue
Components/LiveScoring/LiveStats.vue
Components/LiveScoring/SessionHeader.vue
Components/LiveScoring/EndList.vue
```

### **4.7 Coach Monitoring Components**
```
Components/Coach/LiveSessionCard.vue
Components/Coach/ArcherStatusCard.vue
Components/Coach/PerformanceAlert.vue
```

---

# 5. **Composables (Vue 3 Composition API)**

Reusable logic modules.

```
Composables/useAuth.js
Composables/useTenant.js
Composables/useLiveScoring.js
Composables/useCharts.js
Composables/usePagination.js
Composables/useForm.js
Composables/useNotifications.js
```

---

# 6. **Stores (Pinia)**

State management.

```
Stores/auth.js
Stores/liveSession.js
Stores/coachMonitor.js
Stores/notifications.js
Stores/settings.js
```

---

# 7. **Utilities**

```
Utils/formatDate.js
Utils/formatScore.js
Utils/calcAverages.js
Utils/calcEndStats.js
Utils/calcProgression.js
```

---

# 8. **Layouts & Navigation**

### **8.1 Layout Rules**
- Each user type has its own layout:
  - Archer → `AppLayout.vue`
  - Coach → `CoachLayout.vue`
  - Admin → `AdminLayout.vue`

### **8.2 Navigation**
Claude must generate navigation menus based on role:

#### Archer Menu
- Dashboard
- Training Sessions
- Live Scoring
- Equipment
- Competitions
- Reports

#### Coach Menu
- Dashboard
- Archers
- Live Monitor
- Training Plans
- Reports

#### Coach Menu
- Dashboard
- Members
- Coaches
- Lanes
- Bookings
- Inventory
- Finance
- Reports
- Settings

---

# 9. **Live Scoring UI Specification**

Claude must follow this structure:

### **9.1 Active Session Page**
```
<SessionHeader />
<LiveStats />
<EndInput />
<EndList />
```

### **9.2 End Input Component**
- Arrow inputs (1–6)
- Score buttons (0–10, X)
- Quick tags ("good release", "rushed", "windy")

### **9.3 Live Stats Component**
- Running total
- Average
- X count
- 10 count
- End-by-end chart (optional)

---

# 10. **Coach Live Monitor UI**

Claude must generate:

### **10.1 Live Monitor Page**
```
<LiveSessionCard v-for="session in activeSessions" />
```

### **10.2 LiveSessionCard**
- Archer name
- Current end
- Last end score
- Alerts (performance drop, long idle time)
- Quick notes input

---

# 11. **Reports UI**

### **11.1 Archer Score Progression**
```
<LineChart />
<StatsCard />
<SessionList />
```

### **11.2 Live Session Summary**
```
<EndList />
<BarChart />
<NotesList />
```

### **11.3 Lane Usage Heatmap**
```
<Heatmap />
<Filters />
```

---

# 12. **Frontend Coding Standards**

Claude must follow:

- Vue 3 Composition API
- Script setup syntax
- TailwindCSS utility-first styling
- Reusable components
- No inline CSS
- Use composables for logic
- Use Pinia for shared state
- Use Axios for API calls
- Use async/await
- Use Inertia for navigation

---

# 13. **How Claude Should Generate Frontend Code**

When asked:

- Provide **full Vue components**, not snippets
- Include `<script setup>`
- Include `<template>`
- Include Tailwind classes
- Include props, emits, and composables
- Include example data when needed
- Ensure components are functional and ready to use

---

# 14. **Next Steps for Frontend Development**

1. Generate layouts
2. Generate navigation menus
3. Build Archer Dashboard
4. Build Live Scoring UI
5. Build Coach Live Monitor
6. Build Admin Lane Booking
7. Build Reports pages
