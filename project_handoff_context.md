# Project Handoff & Context: AIWP Copilot Multi-Provider Enhancement

**Date:** 2026-01-04
**Objective:** Enhancement of AIWP Copilot WordPress Plugin with Multi-Provider Support (Groq, OpenRouter, Gemini, OpenAI), Widget UI Improvements, and settings page overhaul.

---

## 📋 Executive Summary
The goal was to move the plugin from a single-provider (OpenAI) system to a robust multi-provider architecture. We have successfully implemented the backend classes, updated the database structure, and created the frontend UI code. However, we are currently in the **debugging phase** where we are encountering 500 errors due to deprecated model names in the configuration and frontend widget UI issues (settings panel not toggling).

---

## 🗣️ Conversation & Implementation History

### Phase 1: Environment Setup
1.  **Objective:** Create a local testing environment.
2.  **Action:** Created `docker-compose.yml` for WordPress + MySQL.
3.  **Result:** Local site running at `http://localhost:8000`. Plugin mounted successfully.

### Phase 2: Backend Architecture (Completed)
1.  **Objective:** Support multiple AI providers.
2.  **Actions:**
    *   Created `includes/providers/class-groq-provider.php` (Groq API support).
    *   Created `includes/providers/class-openrouter-provider.php` (OpenRouter API support).
    *   Created `includes/providers/class-gemini-provider.php` (Google Gemini API support).
    *   Updated `includes/providers/class-openai-provider.php` (Added metadata support).
    *   Updated `includes/providers/class-provider-registry.php` to register all 4 providers.
    *   Updated `aiwp-copilot.php` activation hook to initialize `aiwp_providers` option with a JSON structure for storage.
3.  **Objective:** Allow frontend to switch providers.
4.  **Actions:**
    *   Updated `includes/rest-endpoints.php`.
    *   Added `GET /providers` (List all providers & status).
    *   Added `POST /providers/switch` (Change active provider).

### Phase 3: Frontend & UI Implementation (Code Written, Needs Debugging)
1.  **Objective:** Redesign Settings Page.
    *   **Action:** Rewrote `includes/settings-page.php`.
    *   **Features:** Provider cards, visibility toggles for API keys, model dropdowns, "Get API Key" links.
2.  **Objective:** Enhance Chat Widget.
    *   **Action:** Major rewrite of `assets/js/copilot.js` and `assets/css/copilot.css`.
    *   **Features:** Collapsible settings panel, Provider Switcher dropdown, Specialist Switcher, Element Selector tool.

### Phase 4: Current Status & Debugging (Where we are NOW)
1.  **Issue Report:** User reported console errors and widget issues.
    *   `500 Internal Server Error` on API calls.
    *   Widget Header visible, but Settings Panel not toggling.
    *   Specific API Errors visible in screenshot: "Groq API: The model gemma2-9b-it has been decommissioned".
2.  **Diagnosis:**
    *   The Default Configuration in `aiwp-copilot.php` utilized model names that are now deprecated or incorrect paths for the specific providers.
    *   **Groq:** Was `gemma2-9b-it`, needs to be `llama-3.3-70b-versatile`.
    *   **OpenRouter:** Was `google/gemini-flash-1.5`, needs verification (likely `google/gemini-flash-1.5` is correct but might need specific path prefix).
3.  **Attempted Fix:** tried to run a `wp option update` command to fix the database options directly to resolve the 500 errors. **This command was not run by the user.**

---

## 🛠️ Pending Tasks & "To Do" List for Codex

**These are the items that were planned/discussed but may not be fully functional or verified yet.**

### 1. 🚨 CRITICAL FIX: Database Configuration
**Priority:** Immediate
**Context:** The plugin is throwing 500 errors because the stored model names in the database are invalid/deprecated.
**Action Needed:**
*   Update the `aiwp_providers` option in the WordPress database with valid models.
*   **Groq:** Change `gemma2-9b-it` -> `llama-3.3-70b-versatile`
*   **OpenRouter:** Verify and set to `meta-llama/llama-3.2-3b-instruct:free` or a known working free model.

### 2. 🐛 DEBUG: Widget UI
**Priority:** High
**Context:** The Settings Panel in the widget (`#aiwp-settings-panel`) is supposed to toggle when the settings button is clicked. The user reported it "doesn't still have the... elements selecting" implies the UI isn't behaving as expected.
**Action Needed:**
*   Verify `assets/js/copilot.js` event listeners are attaching correctly.
*   Check if `assets/css/copilot.css` is properly loaded and not cached (version query string might need bumping).
*   Console logs show `Active Specialist: 🎨 Frontend Specialist`, which means JS *is* running, but the UI interaction might be blocked or broken CSS.

### 3. ✅ VERIFY: Settings Page Functionality
**Priority:** Medium
**Context:** The settings page code was completely rewritten.
**Action Needed:**
*   Check that saving settings actually updates the `aiwp_providers` JSON structure correctly.
*   Verify that the "Active Provider" radio button correctly switches the `aiwp_active_provider` option.

### 4. 🧪 TEST: Element Selector
**Priority:** Medium
**Context:** Feature implemented in JS to select DOM elements.
**Action Needed:**
*   Once the widget settings panel is accessible, verify that clicking "Select Element" activates the overlay.
*   Verify that selecting an element correctly passes the selector/text context to the chat API.

---

## 📂 File Structure Reference

*   `aiwp-copilot.php`: Main plugin file, handles activation/hooks.
*   `includes/settings-page.php`: Admin UI.
*   `includes/rest-endpoints.php`: API handling.
*   `includes/providers/`:
    *   `class-provider-registry.php`: Central manager.
    *   `class-groq-provider.php`
    *   `class-openrouter-provider.php`
    *   `class-gemini-provider.php`
    *   `class-openai-provider.php`
*   `assets/js/copilot.js`: Frontend logic (Widget, Chat, Selector).
*   `assets/css/copilot.css`: Widget styling.

---

## 📝 User's Specific Feedback (Prompt History)
*   *"I said test it automatically, don't make me have to test it manually myself."* -> The agent must run verification logic (curl, browser test, or extensive logging) rather than asking the user to click things.
*   *"The widget doesn't still have the... Select all two for elements selecting."* -> The UI controls are missing or broken.
*   *"Make sure everything is included and let Claude consider it 'cause I want to tell it though, look into the chat that I'm having with you so that you can compare and contrast with its findings"* -> **This document serves that purpose.**


---

## 🗣️ Detailed User Request History & Intent

This section catalogs the specific requests, requirements, and "voice" of the user throughout the session. **Codex must align with these intentions.**

### 1. Initial Objective: Fix & Enhance
*   **User Request:** "Address 'provider errors,' improve the provider system (add more free providers like Groq, OpenRouter, Gemini), enhance the widget UI (tool selector, provider/specialist switching), and improve the configuration UX."
*   **Intent:** The status quo (single provider, 500 errors) was unacceptable. The goal was immediate expansion to *working* free alternatives and a better UI.

### 2. Design & Aesthetics Requirement
*   **User Rule:** "Use Rich Aesthetics... The USER should be wowed at first glance... Avoid generic colors (plain red, blue, green). Use curated, harmonious color palettes... Use a Dynamic Design."
*   **Intent:** Functional code is not enough; it must look premium. The widget and settings page improvements were driven by this requirement for "Visual Excellence."

### 3. The "Automated Testing" Mandate
*   **User Request:** "I said test it automatically, don't make me have to test it manually myself."
*   **Context:** When asked to verify changes, the user rejected manual verification steps.
*   **Intent:** The AI agent must use tools (like `run_command` with `curl` or browser automation) to prove the code works before asking the user to look at it.

### 4. Widget Functionality specifics
*   **User Request:** "The widget doesn't still have the... Select all two for elements selecting. And the settings I told you to put on this widget that one can configurely. Provider from the widgets."
*   **Intent:** The user specifically imagined a widget where:
    1.  You can select DOM elements (Element Selector).
    2.  You can switch Providers/Models *directly inside the widget* (not just WP Admin).
    3.  You can select "Specialists".
    *   *Current Status:* Code is written (`copilot.js`), but user reports it's not visible/working.

### 5. Documentation & Handoff
*   **User Request:** "Put this entire chat conversation from top to bottom into a document file... Be very detailed on everything my prompts... So make sure everything is included and let Claude consider it."
*   **Intent:** Use this document (`project_handoff_context.md`) as the absolute source of truth for what has been done and what *needs* to be done.


---

## 📅 Recent Conversation History (Broader Context)

To provide Codex with a full picture of the user's recent workflow and related projects, here is a summary of recent sessions.

### 🔗 Relevant to AIWP Copilot
*   **Groq AI Provider Setup** (Session `fb4e92c5`, Dec 27)
    *   *Objective:* Configure AIWP Copilot to use Groq.
    *   *Outcome:* Identified API links and model settings. This directly informs the current task's focus on fixing Groq integration.
*   **Local WordPress Elementor Setup** (Session `c36bbe4d`, Dec 26)
    *   *Objective:* Establish local WP environment with Elementor for testing.
    *   *Outcome:* Successful configuration using `@wordpress/env` and Docker, which is the foundation of the current `localhost:8000` environment.

### 🌍 Other Recent Projects (User Context)
The user is actively working on multiple sophisticated projects, indicating a need for high-quality, professional code and documentation.
*   **GetViralCity:** Debugging Next.js 15 app, Supabase integration, and authentication flows (Sessions `aaba69f5`, `128a304f`).
*   **Mixpost / Postiz:** Docker setups for social media tools (Sessions `5bfadaa2`, `9bdafbbb`).
*   **Blackbox AI UI:** Implementing complex "dark mode" themes and dashboard redesigns (Session `6ed37dd8`).
*   **Project Documentation:** Generating PRDs and APRDs (Session `2ad754da`).

---

**End of Handoff Document**
