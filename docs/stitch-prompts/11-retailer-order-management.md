# Retailer — Order Management Screen — Google Stitch Prompt

## Purpose
Full order punch management for retailers — view, track, repeat, and manage B2B orders placed with distributors.

## Layout
Filter-bar + tabbed order list + order detail panel (or full-screen detail on mobile).

## Sections & Components

### 1. Top Filter Bar
- Date range picker (start - end date inputs)
- Status filter dropdown: All | Pending | Approved | Shipped | Delivered | Rejected | Cancelled
- Search box: "Search by Order ID or Product"
- Export button: "Download CSV"

### 2. Tab Filters
- All Orders | Pending Approval | Shipped | Delivered | Drafts

### 3. Order Cards/Table
- **Table columns** (desktop): Order ID | Date | Distributor | Items Count | Total (₹) | Status | Actions
- **Card view** (mobile): compact card with same info stacked
- Each row/card has status color indicator (left border)
- Actions dropdown: View Details | Track Shipment | Repeat Order | Cancel Order | Download Invoice

### 4. Order Detail Panel (Slide-in or Full Page)
- **Header**: Order #ORD-P-5678 + status badge + date placed
- **Distributor info**: name, GSTIN, contact, address
- **Items table**: S.No | Product Name | SKU | Qty | Unit Price | Total
- **Totals**: Subtotal → Discount → GST → Shipping → Grand Total
- **Timeline**: vertical flow — Order Placed (time) → Approved (time) → Shipped (time) → Delivered (time)
  - Active step highlighted green, completed steps have checkmark
- **Actions**: Repeat Order | Download Invoice | Contact Distributor | Print

### 5. Track Shipment Modal
- Courier name + tracking number
- Live tracking status: "In transit — Mumbai to Delhi"
- Timeline of shipment events:
  - Picked up | Out for delivery | In transit (with location pins)
- Estimated delivery date
- "Track on Courier Website" link

### 6. Empty State
- Box with order document illustration
- "No orders yet"
- "Place your first bulk order through the Order Punch"
- "Start Ordering" CTA

## Interactions
- Tab switch: smooth fade transition
- Row click: opens detail panel
- Repeat Order: pre-fills order punch with same items + quantities
- Cancel: confirmation modal with reason selector
- Export: generates CSV download
