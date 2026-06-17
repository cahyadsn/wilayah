## 2024-05-20 - Auto-focusing dynamically revealed inputs
**Learning:** Moving focus to newly populated dropdowns significantly improves keyboard navigation flow in multi-step forms, saving users from manually tabbing to the next input.
**Action:** Always consider auto-focusing the next logical input field when an action (like a selection) causes it to appear or populate, provided it doesn't disrupt the user's expected flow.

## 2024-05-20 - ARIA Live regions for AJAX loading
**Learning:** Loading spinners alone are insufficient for screen reader users. Dynamic content updates need ARIA live regions to announce state changes.
**Action:** Consistently use `role="status"` and `aria-live="polite"` on containers that hold loading indicators or dynamic status text to ensure screen readers announce them.
