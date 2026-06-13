# Admin — Returns & Refunds Management Screen — Google Stitch Prompt

## Purpose
Manage all return/replacement requests — approve, reject, schedule pickup, process refunds, and track return analytics.

## Layout
Return request cards + detail modal.

## Sections & Components

### 1. Top Bar
- Search: "Search by Return ID or Order ID"
- Status filter: All | Pending Approval | Pickup Scheduled | Item Received | Refund Initiated | Refunded | Rejected
- Type filter: All | Return | Replacement
- Date range picker

### 2. KPI Row (Compact)
- Total Returns This Month: 24
- Pending Approval: 8 (clickable)
- Avg Refund Time: 2.3 days
- Return Rate: 1.8%

### 3. Return Request Cards (Card Layout, Not Table)
Each card:
- **Return ID**: #RET-5678 | Order #ORD-1234
- **Customer**: name + phone
- **Item**: image + name + qty + reason (e.g., "Expired product received")
- **Type badge**: Return (red) / Replacement (blue)
- **Status timeline**: compact horizontal — Requested → Approved → Pickup → Received → Refunded
  (Grey dots → green dots as steps complete)
- **Requested**: "2 days ago"
- **Actions**: View Details | Approve | Reject | Schedule Pickup | Process Refund

### 4. Return Detail Modal
- **Customer info**: name, email, phone, address
- **Order info**: order ID, date, payment method
- **Items being returned**: table with product + qty + reason + condition
- **Evidence**: uploaded images (max 3, click to enlarge)
- **Customer note**: text
- **Admin notes**: textarea with previous notes listed
- **Timeline**: full detail of all events
- **Action buttons**: Approve | Reject | Mark as Picked Up | Mark as Received | Initiate Refund | Complete Refund

### 5. Approve Modal
- Select action: Return / Replacement
- Pickup address (pre-filled, editable)
- Pickup date + time slot selector (calendar + time dropdown)
- Courier: dropdown or "Let customer arrange" checkbox
- Send update to customer toggle
- "Approved & Schedule Pickup" button

### 6. Refund Modal
- Refund amount (auto-calculated, editable for partial)
- Refund method: Original Method | Wallet | Bank Transfer
- Notes for customer (optional)
- "Process Refund" button

### 7. Reject Modal
- Rejection reason dropdown (required):
  - Item not received
  - Wrong item returned
  - Damaged by customer
  - Return window expired
  - Other (specify)
- Admin note textarea (required)
- "Notify Customer" checkbox
- "Reject Request" red button

### 8. Empty State
- Return box illustration
- "No return requests yet"
- "Return requests from customers will appear here"
