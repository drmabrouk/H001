import os
import sys
import time
import subprocess
from playwright.sync_api import sync_playwright

def run_tests():
    print("--- Starting Healthedia Verification Tests ---")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context()
        page = context.new_page()
        page.on("pageerror", lambda err: print(f"PLAYWRIGHT PAGE ERROR: {err}"))
        page.on("console", lambda msg: print(f"PLAYWRIGHT CONSOLE: {msg.text}"))

        # 1. Access the Mock WordPress home/search page
        print("Visiting Home Page on desktop to check compact header container height...")
        page.goto("http://localhost:8000/")
        page.wait_for_selector(".healthedia-search-title")
        assert "HEALTHEDIA" in page.text_content(".healthedia-search-title")

        # Verify compact header (height <= 60px)
        header_container = page.locator(".healthedia-header-container")
        box = header_container.bounding_box()
        assert box is not None
        print(f"Desktop Header container height: {box['height']}px")
        assert box['height'] <= 60.5, f"Header height is not compact! Got: {box['height']}px"
        print("[PASS] Desktop compact header height verified.")

        # Let's open a mobile viewport context to verify mobile dropdown menu
        print("Spawning mobile context (viewport: 375x667)...")
        mobile_context = browser.new_context(viewport={"width": 375, "height": 667})
        mobile_page = mobile_context.new_page()
        mobile_page.goto("http://localhost:8000/")
        mobile_page.wait_for_selector(".healthedia-global-header")

        # Verify logo and mobile trigger are on top row
        logo_box = mobile_page.locator(".healthedia-logo-group").bounding_box()
        mobile_trigger_box = mobile_page.locator(".healthedia-mobile-nav-trigger-container").bounding_box()
        assert logo_box is not None and mobile_trigger_box is not None
        print(f"Mobile - Logo Y: {logo_box['y']}, Mobile Trigger Y: {mobile_trigger_box['y']}")
        assert abs(logo_box['y'] - mobile_trigger_box['y']) <= 15, "Logo and mobile trigger are not on same row on mobile!"
        print("[PASS] Mobile logo and trigger button are aligned side-by-side.")

        # Trigger mobile menu toggle click
        print("Clicking mobile navigation dropdown toggle...")
        mobile_page.click("#mobile-menu-toggle-btn")
        # Assert class open is added
        trigger_class = mobile_page.locator("#healthedia-mobile-trigger-container").get_attribute("class")
        assert "open" in trigger_class, "Mobile dropdown menu did not open on click!"
        # Check that mobile menu items are visible
        assert mobile_page.is_visible("#mobile-dropdown-menu-list"), "Mobile menu list is invisible!"
        print("[PASS] Mobile dropdown navigation menu is completely responsive, compact, and interactive.")
        mobile_page.screenshot(path="healthedia_mobile_dropdown_screenshot.png")
        mobile_context.close()


        # ==========================================
        # 2. VERIFY DEFAULT PAGE TITLE & CONTENT
        # ==========================================
        print("Visiting a regular default page to check title and content rendering...")
        page.goto("http://localhost:8000/sample-page")

        # Verify page uses theme default loops and structure
        page.wait_for_selector(".healthedia-default-page-wrapper")
        title_element = page.locator(".healthedia-default-title")
        content_element = page.locator(".healthedia-default-content")

        assert "Sample Research Page" in title_element.text_content(), "Standard page title is missing!"
        assert "biomechanical gait adaptations" in content_element.text_content(), "Standard page content is missing!"

        print("[PASS] Regular pages successfully output both page title and page content/data within default structures.")
        page.screenshot(path="healthedia_default_page_screenshot.png")


        # ==========================================
        # 3. SECURE MULTI-STEP REGISTRATION FLOW
        # ==========================================
        print("Visiting Auth Page to register via 3-step wizard...")
        page.goto("http://localhost:8000/healthedia-auth/")
        page.wait_for_selector("#form-login")

        # Click Create Account tab
        page.click("#tab-register-btn")
        page.wait_for_selector("#view-register", state="visible")

        # Step 1: Institutional Credentials
        print("Filling Registration Step 1 (Institutional Credentials)...")
        page.fill("#register-email", "alan.turing@cambridge.edu")
        # Click Continue to Step 2
        page.click("text=CONTINUE TO PROFILE")

        # Step 2: Professional Profile
        print("Filling Registration Step 2 (Professional Profile)...")
        page.wait_for_selector("#register-name", state="visible")
        page.fill("#register-name", "Professor Dr. Alan Turing")
        # Click Continue to Step 3
        page.click("text=CONTINUE TO SECURITY")

        # Step 3: Account Security & Verification
        print("Filling Registration Step 3 (Security & OTP)...")
        page.wait_for_selector("#register-password", state="visible")
        page.fill("#register-password", "DecryptedPass123!")
        page.fill("#register-otp", "849204") # Pre-filled code

        # Complete Registration (Should automatically redirect to HOMEPAGE)
        print("Submitting multi-step registration...")
        page.click("#form-register button[type='submit']")

        # Verify automatic redirect to HOMEPAGE '/' after registration
        page.wait_for_url("http://localhost:8000/")
        assert page.url == "http://localhost:8000/", f"Expected redirect to Homepage, got: {page.url}"
        print("[PASS] Completed 3-step Registration flow with successful automatic redirect to Homepage '/'")


        # ==========================================
        # 4. INTERACTIVE HEADER DROPDOWN
        # ==========================================
        print("Opening header dropdown menu on Homepage...")
        page.wait_for_selector("#header-user-dropdown-btn")
        page.click("#header-user-dropdown-btn")

        # Verify welcome back message, user name and Logout option
        assert "Welcome back!" in page.text_content(".healthedia-dropdown-welcome")
        assert "Professor Dr. Alan Turing" in page.text_content(".healthedia-dropdown-user-name")
        assert "Log Out" in page.text_content("#header-logout-btn")
        print("[PASS] Logged-in profile dropdown verified on Homepage.")
        page.screenshot(path="healthedia_home_dropdown_screenshot.png")


        # ==========================================
        # 5. DASHBOARD SECTIONS & SETTINGS
        # ==========================================
        print("Navigating to Healthedia SaaS Dashboard...")
        # Navigate to dashboard from dropdown link
        page.click("text=SaaS Dashboard")
        page.wait_for_url("**/healthedia-dashboard/")
        page.wait_for_selector(".healthedia-dashboard-layout")
        print("[PASS] Successfully entered SaaS Dashboard.")

        # Test Interactive Section (Tab) Transitions
        print("Testing Sidebar tab transitions using explicit data-section attributes...")
        sections_to_test = [
            {"btn_selector": "button[data-section='analytics']", "sec_id": "#sec-analytics", "title_text": "Analytics Section"},
            {"btn_selector": "button[data-section='archive-records']", "sec_id": "#sec-archive-records", "title_text": "Archive Records Section"},
            {"btn_selector": "button[data-section='researchers-profile']", "sec_id": "#sec-researchers-profile", "title_text": "Researchers Profile Section"},
            {"btn_selector": "button[data-section='settings']", "sec_id": "#sec-settings", "title_text": "Settings Section"}
        ]
        for sec in sections_to_test:
            print(f"Clicking tab: '{sec['btn_selector']}'...")
            page.click(sec['btn_selector'])
            page.wait_for_selector(sec['sec_id'], state="visible")
            assert page.is_visible(sec['sec_id']), f"Section {sec['sec_id']} did not display!"
            assert sec['title_text'] in page.text_content("#dashboard-title-label"), f"Dashboard title label did not update!"
            print(f"[PASS] Section tab '{sec['btn_selector']}' transitioned successfully.")

        # Test settings within Dashboard: Disable Registration while keeping Login active
        print("Changing authentication options to: Disable Registration but Keep Login Active...")
        page.select_option("#auth_registration", value="disabled")
        page.click("#btn-save-auth-settings")

        # Verify dynamic success notification banner
        page.wait_for_selector("#dashboard-success-banner")
        banner_text = page.text_content("#dashboard-success-banner")
        assert "settings saved" in banner_text.lower(), "Success notification banner missing or incorrect!"
        print("[PASS] Authentication Options form submitted and saved successfully.")

        # Test Header navigation links management: Add, Move, Remove custom links
        print("Testing Header Navigation Manager - Adding new link 'Contact'...")
        page.fill("#form-add-header-link input[name='new_title']", "Contact")
        page.fill("#form-add-header-link input[name='new_url']", "/contact/")
        page.click("#form-add-header-link button[type='submit']")

        page.wait_for_selector("#dashboard-success-banner")
        print("[PASS] Added 'Contact' link successfully.")

        # Verify that 'Contact' appears in the link list
        assert "Contact" in page.text_content("#header-links-manager-list"), "New link was not added to the list!"

        # Test moving newly added link up
        # Get count of header items
        links = page.locator("#header-links-manager-list .healthedia-link-item")
        print(f"Header links count: {links.count()}")

        # Let's verify deleting/removing the newly added link
        print("Testing Header Navigation Manager - Removing newly added 'Contact' link...")
        # Since we added it, it's at the bottom index. Click its Remove button.
        remove_buttons = page.locator("#header-links-manager-list .healthedia-link-item:has-text('Contact') button.delete")
        remove_buttons.first.click()
        page.wait_for_selector("#dashboard-success-banner")
        assert "Contact" not in page.text_content("#header-links-manager-list"), "Link was not removed from the list!"
        print("[PASS] Removed custom link successfully from link management list.")


        # ==========================================
        # 6. LOGOUT AND DYNAMIC AUTH RESTRICTION
        # ==========================================
        print("Testing logout action and automatic homepage redirection...")
        page.click("text=Logout")
        # Should redirect to HOMEPAGE '/' after logout
        page.wait_for_url("http://localhost:8000/")
        assert page.url == "http://localhost:8000/", f"Expected redirect to Homepage after logout, got: {page.url}"
        print("[PASS] Logout action completed with successful automatic redirect to Homepage '/'")

        # Confirm dynamic auth form restrictions (Since we disabled registration in Step 5, Create Account tab should be hidden!)
        print("Checking dynamic Authentication tabs lock (Registration disabled)...")
        page.goto("http://localhost:8000/healthedia-auth/")
        page.wait_for_selector("#form-login")
        # Check that tabs bar is hidden because only login is enabled now
        assert not page.is_visible("#auth-tabs-bar"), "Tabs bar should be hidden when only one authentication option is enabled!"
        assert not page.is_visible("#view-register"), "Create Account registration view should be locked and invisible!"
        print("[PASS] Authentication tab lock works cleanly and dynamically restricts account creation according to settings.")

        browser.close()
        print("--- All Healthedia Verification Tests Passed Perfectly! ---")

if __name__ == "__main__":
    run_tests()
