# Admin — User Management Screen — Google Stitch Prompt

## Purpose
Manage all platform users — customers, retailers, distributors. View, filter, search, edit, ban/activate, and view user details.

## Layout
Search + filter bar, tabbed user types, data table with pagination.

## Sections & Components

### 1. Top Bar
- Search: "Search by name, email, phone, or ID..."
- User Type tabs: All | Customers | Retailers | Distributors | Admins
- Status filter: All | Active | Banned | Pending Verification
- "Add New User" button (#005221 filled) — opens create form
- "Export" button (outline)

### 2. User Table
- Columns: Avatar + Name | Email | Phone | Type (badge) | Status | Joined Date | Orders Count | Total Spent | Actions
- Status: Active (green dot), Banned (red dot), Pending (yellow dot)
- Type badges: Customer (grey), Retailer (blue), Distributor (purple), Admin (gold)
- Actions dropdown: View Profile | Edit | Ban/Unban | Delete | Impersonate (dev only)

### 3. User Detail Panel (Slide-in)
- Profile card: avatar, name, email, phone, address, member since
- Activity summary: total orders, total spent, last order date, avg order value
- Recent orders list (last 5)
- Account actions: Reset Password | Send Email | Force Logout | Delete Account (red)
- Notes section: internal admin notes textarea

### 4. Add/Edit User Modal
- Avatar upload (image picker + crop)
- Fields: Name, Email, Phone, Password (auto-generate option), User Type (dropdown), Status
- Address fields (for retailer/distributor)
- GSTIN (retailer/distributor)
- Credit limit (retailer/distributor)
- "Send welcome email" checkbox
- Save / Cancel buttons

### 5. Ban Confirmation Modal
- User name + avatar
- Reason for ban (required textarea)
- Duration: Permanent | 7 days | 30 days | Custom date
- "Notify user via email" checkbox
- "Confirm Ban" red button

### 6. Empty State
- People icon
- "No users found matching your filters"
- "Clear Filters" link

## Interactions
- Type tabs: switch with data animation
- Inline status toggle (ban/unban)
- Search: debounced, shows results as you type
- Export: CSV with selected user type filter
- Delete: two-step confirmation with "Type DELETE to confirm"
