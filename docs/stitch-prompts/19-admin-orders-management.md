# Admin — Orders Management Screen — Google Stitch Prompt

## Purpose
Oversee all platform orders — customer orders, B2B order punch, status management, payment verification, and invoice generation.

## Layout
Filter bar + tabbed view + order detail slide-in panel.

## Sections & Components

### 1. Top Filter Bar
- Search: "Search by Order ID, Customer Name, or Phone"
- Order Type tabs: All Orders | Customer Orders | B2B Orders
- Status filter: All | Pending | Processing | Shipped | Delivered | Cancelled | Returned
- Date range picker
- Payment status: All | Paid | Unpaid | Refunded
- "Export Orders" button

### 2. Orders Table
- Columns: Order ID | Customer | Type | Date | Items | Total (₹) | Payment | Status | Actions
- Type badge: "Customer" (blue) or "B2B" (purple)
- Payment: Paid (green), Unpaid (red), COD (yellow)
- Status pill with color coding
- Actions: View | Update Status | Generate Invoice | Cancel | Edit

### 3. Order Detail Panel
- **Header**: Order ID + date + status + "Print Invoice" button
- **Customer info**: name, email, phone, shipping address
- **Order items table**: image + name + SKU + qty + price + total
- **Pricing summary**: subtotal, discount, shipping, GST, grand total
- **Payment info**: method, transaction ID, payment status, paid date
- **Timeline**: vertical timeline of all status changes with timestamps and admin notes
- **Actions**: Update Status dropdown | Cancel Order | Refund | Contact Customer | Add Note (internal)

### 4. Status Update Modal
- Current status selector: Pending → Processing | Shipped | Cancelled
  - Processing → Shipped | Cancelled
  - Shipped → Delivered | Return Initiated
- Optional note to customer (sent as notification)
- Tracking number + courier field (if shipped)
- "Update Status" button

### 5. Cancel/Refund Modal
- Reason dropdown (required): Customer Request, Out of Stock, Payment Failed, Fraud, Other
- Refund method: Wallet | Original Payment Method | Bank Transfer
- Partial refund: amount input
- "Notify customer" checkbox
- "Confirm Cancel" button

### 6. Invoice Section
- Invoice number, date, order details
- "Download PDF" | "Email Invoice" | "Print" buttons

### 7. Empty State
- Clipboard with order icon
- "No orders found"
- "Orders will appear here once customers start purchasing"
