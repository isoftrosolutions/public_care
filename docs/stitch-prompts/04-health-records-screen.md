# Health Records Screen — Google Stitch Prompt

## Purpose
Centralised health dashboard showing vitals, lab reports, allergies, medications, conditions, and family health history. Acts as the patient's lifelong Ayurvedic health passport.

## Layout
Dashboard-style with summary cards on top and tabbed detail sections below.

## Sections & Components

### 1. Profile Header
- Avatar + name + age + blood group badge (e.g., "B+") + "Last updated: 10 Jun 2026"
- "Switch Profile" button (for family members managed by user)
- "Export All Records" outline button

### 2. Health Summary Cards (4 Cards, 2x2 Grid on Desktop, Scrollable Row on Mobile)
- **Vitals**: BP 120/80 | HR 72 bpm | BMI 22.4 — mini line chart showing trend
- **Lab Reports**: "12 reports" — latest: "Lipid Profile, 5 Jun 2026" — status: Normal / Abnormal
- **Current Medications**: "5 active" — latest 3 pill names with remaining days
- **Upcoming Appointments**: next follow-up date + doctor name + "Reschedule" link

### 3. Tabbed Details Section
- **Vitals History**: table/graph of BP, heart rate, weight, SpO2 over time with date axis
- **Lab Reports**: list of report cards with date, test name, lab name, download PDF — abnormal values in red
- **Medications**: timeline view of past/present medications — dosage, duration, doctor, status
- **Allergies**: list + severity badge (mild/moderate/severe) — user-added with "Add Allergy" button
- **Conditions**: diagnosed conditions + diagnosed date + doctor
- **Family History**: tree-style or list of family members + their conditions

### 4. Add Record FAB (Floating Action Button)
- "+" button bottom-right → expandable options:
  - Add Vitals (BP, HR, weight, SpO2, blood sugar)
  - Upload Lab Report (file picker, auto-fill form)
  - Add Allergy
  - Add Medication

### 5. Empty State Per Section
- Illustration + "No vitals recorded yet. Start tracking with your first check-in."
- "Log Vitals" CTA

## Typography
- Numbers/values: DM Sans Medium (tabular figures)
- Labels: DM Sans Regular
- Section titles: Plus Jakarta Sans semibold

## Color Palette
- #005221 (normal/good)
- #EF4444 (abnormal/alerts)
- #F59E0B (warning/pending)
- #F8FAF5 (background)

## Interactions
- Tap a lab report card → opens full report preview modal with PDF viewer
- Long-press a medication → mark as completed
- Pull-to-refresh syncs latest data
- Share button on any record → generate shareable PDF summary
