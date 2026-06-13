# Distributor Dashboard — Google Stitch Prompt

## Purpose
Full B2B distributor portal — manage retailer accounts, approve orders, update product catalog, set pricing & discounts, track shipments, and view network analytics.

## Layout
Sidebar navigation (same structure as retailer but different icons) + main content dashboard.

## Sections & Components

### 1. Sidebar
- 📊 Dashboard
- 🏪 Retailers (my retailers list)
- 📦 My Products (catalog management)
- 📋 Order Management (approve/reject)
- 🚚 Shipments
- 📈 Analytics
- ⚙ Settings

### 2. KPI Cards Row
- Active Retailers: 24 (↑3 this month)
- Pending Orders: 12 (to approve)
- Revenue This Month: ₹3,45,000
- Products Listed: 156

### 3. Recent Retailers Activity
- List: retailer name + last order date + outstanding balance + status (Active/Dormant)
- Action: "View Profile" | "Contact"

### 4. Pending Approvals Widget
- Order ID | Retailer | Items | Amount | "Approve" (green) | "Reject" (red) buttons inline
- "View All" link

### 5. Order Management Page
- Table: Order ID | Retailer | Date | Items | Total (₹) | Status | Actions
- Actions per row: Approve | Reject | Modify | View
- Status badges: Pending (yellow), Accepted (green), Shipped (blue), Delivered (grey)
- **Approval modal**: Confirm approval + "Set expected dispatch date" (date picker)
- **Rejection modal**: Required reason dropdown + optional note to retailer

### 6. Product Catalog Management
- Grid/list of products with: image | name | SKU | MRP | Distributor Price | Stock | Status (Active/Inactive)
- Actions: Edit | Enable/Disable | Delete
- **Add Product**: form with fields — name, description, category, SKU, MRP, price, unit, stock, images, HSN code, GST%
- Bulk upload: "Upload CSV" button
- Price update: inline editable cells

### 7. Shipment Tracking
- Table: Order ID | Retailer | Courier | Tracking # | Status | Action
- Status: Yet to Ship | Shipped | In Transit | Delivered
- Ship action: "Enter Tracking Number" + courier select
- Bulk ship: "Mark Multiple as Shipped"

### 8. Retailer Management
- List of retailers: name, business name, GSTIN, contact, address, credit limit, outstanding
- "Add Retailer" form: business name, owner name, email, phone, GSTIN, address, credit limit
- Retailer detail: profile + order history + outstanding payments

### 9. Analytics
- Revenue trend chart (line, monthly)
- Top retailers bar chart
- Product-wise sales pie chart
- Monthly order count area chart
- "Export Report" button

### 10. Settings
- Profile: business name, logo, contact, address, GSTIN
- Payment: bank details, UPI ID
- Delivery: default courier, shipping zones
- Commission: default margin % set on products
- Notification preferences: new order, order cancelled, payment received

## Color Palette
- #005221 (primary)
- #D4AF37 (accent — revenue, gold)
- #10B981 (approve, active)
- #EF4444 (reject, overdue)
- #F59E0B (pending)

## Interactions
- Inline approve/reject on table rows without page reload
- Product price: double-click to edit inline
- Drag-and-drop product images
- Bulk actions via checkbox selection
