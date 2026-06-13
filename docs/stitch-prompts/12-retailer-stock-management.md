# Retailer — Stock Management Screen — Google Stitch Prompt

## Purpose
Inventory management for retailer — view current stock, set reorder levels, receive low-stock alerts, and track stock movements.

## Layout
Search + filter bar at top, product inventory table below, and stock movement history modal.

## Sections & Components

### 1. Top Controls
- Search: "Search by product name, SKU, or category..."
- Category filter dropdown
- Stock status filter: All | In Stock | Low Stock (red) | Out of Stock (grey)
- "Download Inventory Report" button (outline)

### 2. Filters Row
- Sort by: Name | Stock | Price | Last Updated
- View toggle: Table / Grid

### 3. Inventory Table
- Columns: Image (small) | Product Name | SKU | Category | Current Stock | Reorder Level | Status | Actions
- Status:
  - ✅ In Stock (green, >reorder level)
  - ⚠ Low Stock (yellow, ≤ reorder level)
  - ❌ Out of Stock (red, 0)
- Actions: "Edit" (pencil) | "Adjust Stock" (+/-) | "View History" | "Reorder Now"

### 4. Stock Adjustment Modal (Quick Edit)
- Product name + current stock display
- Adjustment type: "Add Stock" / "Remove Stock"
- Quantity input with + and - buttons
- Reason textarea (optional): "Damaged", "Expired", "Sold", "Returned to Distributor", "Manual count"
- "Save Adjustment" button
- Shows updated stock count live

### 5. Stock Movement History Modal
- Product name + SKU header
- Table: Date | Type (Addition/Removal) | Quantity | Previous Stock | New Stock | Reason | Adjusted By
- Date range filter
- "Export" button

### 6. Low Stock Alerts Widget
- Alert card: "5 products are running low on stock"
- Expandable list:
  - Product | Current Stock | Reorder Level | "Reorder" button
- "Dismiss All" link

### 7. Reorder Level Settings (Inline Edit)
- Click reorder level cell → editable input
- Save on blur or Enter
- Helper: "We'll alert you when stock falls below this level"

### 8. Empty State
- Shelves illustration
- "Your inventory is empty"
- "Add products from your distributor catalog to start managing stock"
- "Browse Distributor Products"

## Interactions
- Quick stock adjust: +/- buttons with animated number change
- Status change: real-time color update
- Reorder click: navigates to order punch with product pre-selected
- Export: CSV with stock levels
