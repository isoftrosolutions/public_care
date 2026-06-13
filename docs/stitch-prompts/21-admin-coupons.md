# Admin — Coupons & Offers Management Screen — Google Stitch Prompt

## Purpose
Create and manage promotional coupons, discounts, offer campaigns, and flash sales.

## Layout
Active offers dashboard + coupon table + create/edit form.

## Sections & Components

### 1. Active Campaigns Row
- Carousel/scrolling cards showing currently active campaigns:
  - "Monsoon Sale — 25% off all Churnas" — ends in 3 days
  - "New User Offer — ₹100 off first order" — ends in 12 days
  - "Free Delivery — Orders above ₹500" — active
- Each card: campaign name, discount, end date, status, "View" button

### 2. Tab Filters
- All | Active | Scheduled | Expired | Disabled

### 3. Coupon/Offer Table
- Columns: Code | Type | Value | Min Order | Usage | Valid Till | Status | Actions
- Type: Percentage (%) / Flat (₹) / Free Shipping
- Usage: "45 / 100 used" (progress bar)
- Status: Active (green), Scheduled (blue), Expired (grey), Disabled (red)
- Actions: Edit | Duplicate | Toggle Status | Delete

### 4. Create/Edit Coupon Form
- **Basic**: Coupon Code (auto-generate + manual override), Description (internal)
- **Discount Type**: Percentage | Flat Amount | Free Shipping
- **Value**: discount value or percentage input
- **Conditions**: Min Order Value, Max Discount Cap
- **User Scope**: All Users | New Users Only | Existing Users Only | Specific Users (user selector)
- **Product Scope**: All Products | Specific Categories (multi-select) | Specific Products (search & select)
- **Usage Limits**: Total Uses (number), Per User Limit
- **Validity**: Start Date (date picker) + End Date (date picker)
- **Other**: "Stackable with other offers" toggle, "Show on homepage" toggle
- "Save" | "Save & Create Another"

### 5. Offer Campaign Builder
- Campaign name
- Banner image upload (with preview)
- Start / End date
- Apply to existing coupon or Create new coupon
- Publish status: Draft / Scheduled / Active

### 6. Empty State
- Ticket with discount icon
- "No coupons created yet"
- "Create your first coupon to boost sales"
- "Create Coupon" CTA
