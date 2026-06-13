# Language Selection Screen — Google Stitch Prompt

## Purpose
Language picker for platform localization. Supports Indian regional languages + English. User preference is saved to profile and persisted across sessions.

## Layout
Grid of language cards, searchable. Full-screen modal or separate page.

## Sections & Components

### 1. Page Header
- Left: back arrow (if modal) or "Language" heading
- Subtitle: "Choose your preferred language" / "अपनी पसंदीदा भाषा चुनें"

### 2. Search Bar
- Search icon + input: "Search language..."
- Filters languages in real-time as user types

### 3. Language Grid
- 3 columns on desktop, 2 on tablet, 1 on mobile
- Each language card:
  - Language name in that language's script (e.g., "हिन्दी", "தமிழ்", "తెలుగు", "മലയാളം", "ಕನ್ನಡ", "বাংলা", "मराठी", "ગુજરાતી", "ਪੰਜਾਬੀ", "ଓଡ଼ିଆ", "English")
  - Romanized name below in small grey text (e.g., "Hindi", "Tamil", "Telugu")
  - Flag icon or region indicator (optional: small Indian state map indicator)
  - Radio selection (circle on right, fills green when selected)
- Selected language has #005221 border (2px) + green checkmark

### 4. Suggested Languages Row
- "Popular" section at top with 4-5 most common languages:
  - English | हिन्दी | తెలుగు | தமிழ் | বাংলা
- Larger cards or horizontal scroll row

### 5. Save Button
- "Continue in [Language]" — full-width #005221 button at bottom
- Subtitle: "You can change language anytime from Settings"

### 6. Empty State
- "No languages matching your search"
- "Try searching in English or your native script"

## Languages List (Full)
1. English
2. हिन्दी (Hindi)
3. తెలుగు (Telugu)
4. தமிழ் (Tamil)
5. മലയാളം (Malayalam)
6. ಕನ್ನಡ (Kannada)
7. বাংলা (Bengali)
8. मराठी (Marathi)
9. ગુજરાતી (Gujarati)
10. ਪੰਜਾਬੀ (Punjabi)
11. ଓଡ଼ିଆ (Odia)
12. संस्कृत (Sanskrit)

## Typography
- Language names: Plus Jakarta Sans regular, displayed in native script
- Romanized: DM Sans regular, smaller, grey (#6B7280)
- Section headers: DM Sans medium, uppercase, small

## Color Palette
- Selected: #005221 border + checkmark
- Cards: white bg, subtle shadow
- Hover: #F0FDF4 (very light green)

## Interactions
- Tap card → selects language (deselects previous)
- Selected state: smooth border color transition
- Search: debounced filter, results update instantly
- Save: applies language, redirects to home with new locale
- Back: returns to previous page without applying
