# Splash / Welcome Screen — Google Stitch Prompt

## Purpose
Animated brand intro screen shown on first visit (or after logout). Reinforces Ayurviro's positioning as a complete Ayurvedic healthcare platform.

## Layout
Full-screen centred layout with animated elements entering sequentially.

## Sections & Components

### 1. Full-Screen Background
- Subtle gradient from #005221 (top) to #003818 (bottom)
- Or: soft leaf/dot pattern overlay (very subtle, 5% opacity)
- No images — minimal brand intro

### 2. Brand Animation (Center, Sequential Entries)
- **Step 1**: Leaf icon (🌿 or custom SVG leaf) — fades in + gentle scale (1.5s)
- **Step 2**: "Ayurviro" logo text — white, Plus Jakarta Sans bold, 48px — slides up with fade (2.5s)
- **Step 3**: Tagline — "Your Complete Ayurvedic Healthcare Platform" — DM Sans, 18px, opacity 70% (3.5s)

### 3. Feature Badges (Bottom Section, Appear at 4.5s)
- Three pills showing core offerings:
  - 🏥 Medicine Delivery
  - 📹 Video Consult
  - 🧪 Lab Tests
  - 🤖 AI Health Assistant
  - 🚚 B2B Order Punch
- Each pill: white bg with #005221 text, rounded-full, px-5 py-2

### 4. CTA Buttons (Appear at 5.5s)
- "Continue to Ayurviro" — white filled button (#D4AF37 accent hover), large, prominent
- Below: small "By continuing, you agree to our Terms & Privacy Policy" text
- "Already have an account? Log in" link

### 5. Loading State (If Assets Load)
- Subtle progress bar at top (green, #005221)
- Or: leaf icon pulsing gently

## Typography
- Logo: Plus Jakarta Sans bold, white
- Tagline: DM Sans regular, light grey/white at 70%
- CTAs: DM Sans semibold

## Color Palette
- Background: #005221 → #003818 gradient
- Text: White (#FFFFFF)
- Pills: White bg, #005221 text
- CTA: White bg → #D4AF37 on hover

## Interactions
- Sequential entrance: CSS keyframe animations with staggered delay
- Continue button: subtle scale + glow on hover
- Fade transition to main app (no flash)
- Skip animation: tap anywhere during animation to skip to CTAs immediately

## States
- First visit: full animation sequence (6s)
- Returning user: skip animation, directly show CTAs or redirect to home
- Post-logout: show CTAs immediately, skip animation
