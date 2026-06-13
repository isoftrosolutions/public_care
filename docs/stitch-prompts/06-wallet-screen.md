# Wallet Screen — Google Stitch Prompt

## Purpose
Digital wallet for storing refunds, cashback, and making payments within Ayurviro. Shows balance, transaction history, add money option, and linked payment methods.

## Layout
Balance hero card at top, tabbed transaction feed below.

## Sections & Components

### 1. Balance Hero Card (Prominent, #005221 Gradient Background)
- Large white rupee amount: "₹1,250.00"
- "Available Balance" label
- Two buttons side by side:
  - "Add Money" (white filled button)
  - "Send to Bank" (white outline button)
- Small text: "Get 5% cashback on wallet payments"

### 2. Quick Stats Row (3 inline cards)
- Total Cashback Earned: ₹350
- Total Saved: ₹520 (from wallet payments)
- Active Offers: 3

### 3. Tab Filters
- All | Credit | Debit | Cashback | Refund

### 4. Transaction List
- Each item is a row with:
  - Left icon: green up-arrow (credit) or red down-arrow (debit)
  - Title: e.g., "Order #ORD-1234 Refund", "Wallet top-up", "Cashback - Summer Sale"
  - Subtitle: date + time
  - Amount: "+₹250.00" (green) or "-₹499.00" (red) — bold
  - Status badge: Successful (green) / Pending (yellow) / Failed (red)
- Grouped by date: "Today", "Yesterday", "This Week", "This Month"

### 5. Add Money Modal
- Preset amounts: ₹100 | ₹200 | ₹500 | ₹1000 | ₹2000 (pill buttons)
- Custom amount input
- Select payment method: UPI, Card, Net Banking
- "Add ₹XXX to Wallet" CTA
- "You will save ₹X using wallet payment" helper text

### 6. Send to Bank Modal
- Enter amount input
- Select saved bank account (or "Add New Account")
- IFSC code + Account number fields (for new)
- Beneficiary name (auto-filled from account name)
- "Initiate Transfer" button
- Note: "Transfers take 1-3 business days"

### 7. Empty State
- Wallet icon with coin illustration
- "Your wallet is empty"
- "Add money to start earning cashback on every payment!"
- "Add Money" button

## Typography
- Balance: Plus Jakarta Sans bold, 40px
- Transaction amounts: DM Sans bold
- Labels: DM Sans regular

## Interactions
- Pull-to-refresh refreshes transaction list
- Tap transaction → detail modal with order link
- Add Money: preset buttons animate to fill when selected
- Transfer: loading bar during processing
