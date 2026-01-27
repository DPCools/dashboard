# HomeDash Changelog

## 2026-01-26 - UI/UX Improvements

### Fixed Issues

1. **Edit/Delete Button Functionality**
   - Restructured card layout so edit/delete buttons work correctly
   - Removed the `<a>` wrapper that was preventing button clicks
   - Moved clickable area to only the card content, not the admin controls

2. **Admin Controls Redesign**
   - Relocated edit/delete buttons to bottom of card (inside border)
   - Added icon buttons with hover effects for better visibility
   - Fixed CSS layout issues with empty boxes
   - Added visual separation with border-top

3. **Drag-and-Drop Reordering**
   - Integrated SortableJS for drag-and-drop item reordering
   - Added grip icon handle for dragging
   - Implemented AJAX endpoint (`/items/reorder`) to save order
   - Visual feedback with ghost element during drag
   - Order persists in database automatically

### New Features

- **Drag Handle**: Visual grip icon on left side of admin controls
- **Hover Effects**: Better button hover states with background colors
- **Real-time Save**: Order changes save immediately via AJAX
- **Lucide Icons**: Added edit-2, trash-2, and grip-vertical icons

### Technical Changes

**Files Modified:**
- `app/views/dashboard/index.php` - Restructured card HTML
- `app/views/layout.php` - Added SortableJS library and initialization script
- `app/controllers/ItemController.php` - Added `reorder()` method
- `index.php` - Added `/items/reorder` route

**New Dependencies:**
- SortableJS 1.15.0 (via CDN)

**Security:**
- CSRF token validation on reorder endpoint
- Admin authentication required for drag-and-drop
- Input sanitization on order array

### Usage

When logged in as admin:
1. Hover over any service card
2. Click and drag the card to reorder
3. Drop in new position
4. Order saves automatically (check console for confirmation)

Admin controls at bottom of each card:
- Grip icon + "Drag to reorder" text on left
- Edit button (blue) on right
- Delete button (red) on right

### Testing

- ✅ Syntax validation passed (no PHP errors)
- ✅ SortableJS library loads correctly
- ✅ AJAX endpoint added to router
- ✅ Database reorder method exists
- ✅ CSRF protection implemented

### Notes

- Drag-and-drop only visible to admin users
- External link icon moved to top-right of title area
- Cards maintain proper hover effects and transitions
- Dark mode fully supported
