# Retailer Dashboard — Google Stitch Prompt

## Purpose
B2B retailer panel for managing inventory, viewing distributor products, placing bulk orders, tracking order-punch history, and viewing sales analytics.

## Layout
Sidebar navigation + main content area. Dashboard-first with KPI cards.

## Sections & Components

### 1. Sidebar Navigation
- Collapsed on mobile (hamburger), expanded on desktop
- Menu items with icons:
  - 📊 Dashboard (active by default)
  - 📦 My Inventory
  - 🚚 Place Order (Order Punch)
  - 📋 Order History
  - 🏢 Distributors
  - 📈 Reports
  - ⚙ Settings
- Bottom: "Switch to Customer Mode" link | Logout

### 2. Top Bar
- Store/business name + GSTIN badge
- Notification bell (order approval alerts, stock alerts)
- "Balance: ₹12,500" (credit limit indicator)

### 3. KPI Cards Row (4 cards)
- Total Orders This Month: 24 (↑12% vs last month)
- Pending Approvals: 5 — "Orders awaiting distributor approval" + "View" CTA
- Low Stock Items: 8 — "Items below reorder level" + "Reorder" CTA
- Credit Used: ₹45,000 / ₹1,00,000 — progress bar

### 4. Recent Orders Table
- Columns: Order ID | Date | Distributor | Items | Total | Status | Action
- Status badges: Pending (yellow), Approved (green), Shipped (blue), Delivered (grey), Rejected (red)
- Action: View Details dropdown
- "View All Orders" link at bottom

### 5. Quick Order Punch
- Mini product search bar: "Search products by name or SKU..."
- Recent/Popular products quick-add pills
- Cart summary mini-widget: "3 items | ₹4,500" + "Place Order" button

### 6. Distributors List Widget
- Avatar + name + "Active" status dot
- Contact number + email
- Minimum order value: ₹5,000
- "View Products" button

### 7. Low Stock Alert Section
- Product | Current Stock | Reorder Level | Suggested Order
- Each row: red stock indicator if below reorder level
- "Add to Order Punch" action per item

## Color Palette
- #005221 (primary, positive)
- #D4AF37 (accent)
- #F59E0B (pending/warning)
- #EF4444 (critical stock)
- #F8FAF5 (bg)

## Typography
- Sidebar: DM Sans medium
- KPI numbers: Plus Jakarta Sans bold, 32px
- Table: DM Sans regular

## Interactions
- Sidebar: active item highlighted with left green border
- Table rows: hover highlight
- Quick order: inline add/remove without page reload
- Credit bar: color changes (green >70%, yellow 30-70%, red <30%)
