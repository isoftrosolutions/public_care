# Admin Dashboard — Google Stitch Prompt

## Purpose
Full platform admin panel — system-wide metrics, user management, order oversight, content management, and platform analytics.

## Layout
Sidebar (expanded) + top bar + content grid. Dark admin sidebar, light content area.

## Sections & Components

### 1. Sidebar Navigation
- Logo at top (Ayurviro Admin)
- Menu with active state indicator:
  - 📊 Dashboard
  - 👥 Users (customers / retailers / distributors)
  - 🥼 Doctors
  - 📦 Products
  - 🏪 Categories
  - 📋 Orders
  - ↩ Returns & Refunds
  - 📅 Appointments
  - 🧪 Lab Tests
  - 💊 Prescriptions
  - 💳 Payments
  - 📢 Coupons & Offers
  - 📰 Blog Posts
  - 📄 Pages (CMS)
  - ⚙ Settings
- Bottom: "View Site" link | Logout

### 2. Top Bar
- Search bar (global search across users, orders, products)
- Right: Notifications bell (badge count), "Admin" avatar dropdown (Profile, Settings, Logout)

### 3. KPI Cards (6 cards, 3x2 grid)
- **Total Revenue**: ₹12,45,000 (↑15.3% vs last month)
- **Total Orders**: 1,245 (↑8.2%)
- **Total Users**: 5,680 (↑12.1%)
- **Active Doctors**: 24
- **Pending Returns**: 8 (clickable, opens returns tab)
- **New Registrations Today**: 34

### 4. Revenue Chart
- Area chart: 6-month revenue trend
- X: months, Y: revenue in ₹
- Controls: toggle between "Revenue" / "Orders" / "Users"
- Download chart as image button

### 5. Recent Orders Table (Compact)
- Order ID | Customer | Date | Amount | Status | Action
- Status with colored pills
- "View All Orders" footer link

### 6. Quick Stats Cards (Bottom Row)
- Top Selling Products (top 5 list)
- Sales by Category (donut chart)
- Browser/Device breakdown (mini pie)
- User signup trend (mini sparkline)

## Color Palette
- Sidebar: #1A1A2E (dark navy)
- Content: #F8FAF5 (warm white)
- Primary: #005221
- Accent: #D4AF37
- Status colors: green (success), yellow (pending), red (failed/return), blue (processing)

## Typography
- Sidebar items: DM Sans medium, 14px
- KPI values: Plus Jakarta Sans bold, 36px
- Table data: DM Sans regular, 13px

## Interactions
- Chart: date range selector, hover tooltip
- KPI click: navigates to detail page
- Table row click: opens order detail
- Global search: dropdown results with keyboard navigation
