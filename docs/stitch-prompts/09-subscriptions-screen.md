# Subscriptions Screen — Google Stitch Prompt

## Purpose
Monthly medicine subscription management — create, pause, skip, cancel, and track recurring Ayurvedic medicine deliveries.

## Layout
Dashboard: active subscription cards at top, history below in list format.

## Sections & Components

### 1. Page Header
- "My Subscriptions" heading
- Right: "Create New Subscription" button (#005221 filled)
- Subtitle: "Never run out of your essential Ayurvedic medicines"

### 2. Active Subscription Cards
- Each card is a summary block:
  - **Product image** + name + dosage info
  - **Delivery schedule**: "Every 30 days" with calendar icon
  - **Next delivery date**: "15 Jul 2026" + countdown: "3 days left"
  - **Price per cycle**: "₹499/cycle"
  - **Status badge**: Active (green) / Paused (yellow) / Cancelled (red)
  - **Savings badge**: "Save 15%" (#D4AF37)
- Action buttons row:
  - Skip Next Delivery | Pause Subscription | Edit Schedule | Cancel

### 3. Delivery Timeline
- Horizontal visual timeline showing past deliveries (grey, checkmark) and upcoming (green)
- Each dot: date + "Delivered" or "Scheduled"
- "Next delivery in 5 days" highlight

### 4. History Section
- Tab filters: Active | Paused | Completed | Cancelled
- List of past subscription cycles: date range + status + amount

### 5. Create / Edit Subscription Modal
- Step 1: Select product (search by name or category)
- Step 2: Select delivery frequency: Every 15 days | Every 30 days | Every 45 days | Every 60 days
- Step 3: Select quantity per cycle
- Step 4: Choose start date (calendar picker)
- Step 5: Select delivery address (from saved addresses)
- Step 6: Payment method for auto-pay
- Review summary: product, frequency, quantity, next delivery, total per cycle
- "Start Subscription" CTA

### 6. Skip/Pause/Cancel Confirmation Modal
- **Skip**: "Skip next delivery on 15 Jul?" — "Delivery will resume on 15 Aug"
- **Pause**: "Pause subscription?" — "Select duration: 1 month | 2 months | 3 months | Custom"
- **Cancel**: "Cancel subscription?" — reason dropdown + confirmation
- Each with secondary action text

### 7. Empty State
- Calendar + medicine bottle illustration
- "No subscriptions yet"
- "Subscribe to your monthly medicines and get 15% off every cycle!"
- "Start a Subscription" CTA

## Typography
- Frequency: DM Sans medium
- Dates: DM Sans regular
- Savings: Plus Jakarta Sans semibold, #D4AF37

## Interactions
- Create flow: step-by-step wizard with progress indicator (dots)
- Skipping date: calendar animation showing skipped date
- Pause: duration selector with pill buttons
- Cancel: reason dropdown animates open
- Edit: opens same modal pre-filled
