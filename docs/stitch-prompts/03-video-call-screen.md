# Video Call Screen — Google Stitch Prompt

## Purpose
In-browser video consultation interface between patient and Ayurvedic doctor. Includes call controls, chat, prescription quick-send, and session timer.

## Layout
Full-screen video grid with overlay controls. Two modes: portrait (mobile-optimized) and landscape (desktop split).

## Sections & Components

### 1. Video Grid (Primary Area)
- **Doctor video**: large main tile (desktop) or full width (mobile)
- **Self video**: picture-in-picture (bottom-right, draggable, ~120x160px)
- Doctor's name + specialization overlay at top-left of tile (semi-transparent black bar)
- Connection quality indicator: green (excellent), yellow (okay), red (poor) dot top-right
- Recording indicator: red dot + "Recording" if enabled

### 2. Top Bar (Always Visible)
- Back arrow + "Consultation with Dr. Priya Sharma"
- Timer: "00:12:34" (elapsed) / "15:00" (total)
- End Call button (red, prominent)
- Minimize button (collapses to floating bubble)

### 3. Bottom Control Bar (Semi-Transparent, Auto-Hide After 3s)
- Toggle mic (on/off)
- Toggle camera (on/off)
- Switch camera (front/rear)
- Speaker toggle (speaker/earpiece)
- **More menu**: Report issue, Flip camera, Screen share
- Chat bubble button (opens side panel) — shows unread count badge
- Prescription button (quick open prescription composer)

### 4. Chat Panel (Slide-in from Right)
- Doctor's messages + patient replies in chat bubble format
- Pre-consultation intake form summary at top
- Text input + send button + attachment (camera roll / document)
- Quick replies: "I'm feeling dizzy", "Can you repeat that?", "Send me the prescription"
- Doctor-typed messages highlighted with green left border

### 5. Prescription Composer (Slide-in Panel)
- Doctor-facing interface (patient sees "Doctor is writing prescription...")
- Medicine name input with autocomplete
- Dosage dropdowns: frequency, duration, timing
- Add medicine button (repeating rows)
- Notes field
- Sign & Send button (triggers patient-side "Prescription received!" card)

### 6. End Call Overlay
- Call duration: "You consulted for 12 minutes"
- "Rate your consultation" — 5 stars (interactive)
- "Leave a review" textarea
- Options: "Book Follow-up" | "View Prescription" | "Return to Dashboard"

### 7. Waiting Room (Pre-Call)
- Doctor name + photo + "Dr. Priya Sharma will join shortly"
- Animated pulsing avatar
- "Waiting time: ~2 minutes"
- Connection test: mic + camera green checks
- Background blur toggle

## Typography
- Timer: DM Sans Mono (monospace for countdown)
- Names: Plus Jakarta Sans semibold
- Chat: DM Sans regular

## Color Palette
- #005221 (primary, used for connected state)
- #EF4444 (end call, disconnect)
- #1F2937 (overlay bars)
- #10B981 (connected quality)

## Interactions
- Double-tap to zoom doctor video
- Swipe up on self-video to resize
- 5s inactivity hides controls
- Tap anywhere shows controls
- End call: confirmation dialog "Are you sure?"
- Network drop: reconnection overlay with spinner + "Reconnecting..."
