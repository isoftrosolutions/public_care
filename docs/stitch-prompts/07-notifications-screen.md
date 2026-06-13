# Notifications Screen — Google Stitch Prompt

## Purpose
Centreal notification centre showing all alerts: order updates, appointment reminders, health tips, offers, and system messages.

## Layout
Full-page notification feed with tabs and per-item actions.

## Sections & Components

### 1. Page Header
- "Notifications" heading
- Right: "Mark all as read" link (appears when unread exist)
- Right: settings gear icon (links to notification preferences in Profile)

### 2. Tab Filters
- All | Unread | Offers | Orders | Health | Appointments

### 3. Notification List
- Each notification card:
  - Left icon: contextual — box (orders), calendar (appointments), heart (health), tag (offers), bell (system)
  - Icon has colored circle background matching type:
    - Orders: #005221
    - Appointments: #10B981
    - Health: #8B5CF6
    - Offers: #F59E0B
    - System: #6B7280
  - Title: bold if unread, regular if read
  - Body: 1-2 line preview
  - Timestamp: relative ("2 min ago", "Yesterday at 3:45 PM")
  - Right: unread dot (blue circle) if unread, swipe-left hint (mobile)
  - Action CTA if applicable: "Track Order" | "View Appointment" | "Shop Now" | "See Offer"
- Grouped by date sections: "Today", "Yesterday", "This Week", "Earlier"

### 4. Notification Type Examples
- **Order**: "Your order #ORD-1234 has been shipped!" + Track Order CTA
- **Appointment**: "Reminder: Dr. Priya Sharma tomorrow at 10:00 AM" + View Details
- **Health**: "Time for your Ashwagandha dose! 1 capsule after breakfast."
- **Offer**: "30% off on Triphala Churna — Today only!" + Shop Now
- **System**: "Your prescription has been verified and approved."
- **Wallet**: "₹250 added to your wallet. New balance: ₹1,250"

### 5. Notification Detail Modal
- Full notification content
- Action button (if applicable)
- "Delete notification" link at bottom

### 6. Empty State
- Bell icon with sparkle
- "All caught up!"
- "You'll see notifications about orders, health tips, and offers here."
- "Explore Ayurviro" CTA

### 7. Loading State
- 5 skeleton cards (circle + 2 lines each, shimmer animation)

## Interactions
- Swipe left (mobile) → reveals "Mark as read" / "Delete" buttons
- Tap → open detail or navigate to relevant page
- Long press → select mode for bulk action (mark read / delete)
- Pull-to-refresh reloads notifications
- Badge on header icon (in nav) clears when all read

## Typography
- Title: DM Sans medium
- Body: DM Sans regular, grey (#6B7280)
- Timestamp: DM Sans regular, smaller, light grey
