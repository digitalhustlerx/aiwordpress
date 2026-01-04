# Browser Automation Verification Plan

## Objective
To automatically verify the AIWP Copilot widget functionality and ensure the UI issues (specifically the Settings Panel not toggling) are resolved without requiring manual user testing.

## Test Environment
- **Target URL:** `http://localhost:8000/wp-admin/post.php?post=1&action=edit` (or any valid post edit URL)
- **Credentials:** `admin` / `password`

## Automated Test Scenarios

### 1. Widget Initialization & Visibility
*   **Step 1:** Navigate to the WordPress Post Editor.
*   **Step 2:** Wait for `#aiwp-copilot-widget` to appear in the DOM.
*   **Verification:** visible status of the widget container.

### 2. Settings Panel Toggle (🚨 Critical Fix)
*   **Context:** User reported: *"The widget doesn't still have the... settings I told you to put... settings panel not toggling."*
*   **Step 1:** Locate the Settings Toggle button (`#aiwp-settings-toggle`).
*   **Step 2:** Click the button.
*   **Verification:** Check if `#aiwp-settings-panel` changes from `display: none` to visible.
*   **Success Condition:** Elements like `#aiwp-provider-switcher` and `#aiwp-element-selector-toggle` are strictly visible to the user.

### 3. Provider Switching Logic
*   **Step 1:** Ensure Settings Panel is open.
*   **Step 2:** Change value of `#aiwp-provider-switcher` to a different provider string (e.g., switching from Groq to OpenRouter).
*   **Verification:**
    *   Observe chat message area for system message: *"✅ Switched to..."*
    *   (Optional) Check Network tab for `POST /aiwp/v1/providers/switch` request status 200.

### 4. Element Selector Functionality
*   **Step 1:** Click the "Select Element" button (`#aiwp-element-selector-toggle`).
*   **Verification:**
    *   `body` tag should have class `aiwp-selector-active`.
    *   Button text should change to "Cancel Selection" (or indicated active state).
*   **Step 2:** Simulate Hover over the Post Title (`.editor-post-title__input` or similar).
*   **Verification:** The hovered element should receive the `.aiwp-highlight` class (visual blue outline).
*   **Step 3:** Click the element.
*   **Verification:**
    *   Widget displays "Selected element: [selector]".
    *   Chat selection mode deactivates.

## Execution Instructions for AI Agent
1.  Use the `browser_subagent` or valid browser tool.
2.  **Do not ask the user** to verify. Run the checking logic (e.g., `document.querySelector(...).style.display`) and report the boolean result.
3.  If any step fails, report the specific DOM state (e.g., "Panel display is still 'none' after click").
