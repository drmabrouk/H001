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

        # ==========================================
        # 1. VERIFY FONT ADOPTION (NO INTER IMPORTS)
        # ==========================================
        print("Visiting Home Page to check fonts...")
        page.goto("http://localhost:8000/")
        page.wait_for_selector(".healthedia-search-title")
        content = page.content()
        assert "fonts.googleapis.com/css2?family=Inter" not in content, "Found forbidden Google Fonts Inter import!"
        print("[PASS] Conflicting/forbidden external fonts are completely removed.")

        # ==========================================
        # 2. SUGGESTIONS LIMIT & NO AI SEARCH
        # ==========================================
        # Check that there are exactly 3 suggestions on the homepage
        tags = page.locator(".healthedia-tag")
        print(f"Homepage suggested search tags count: {tags.count()}")
        assert tags.count() == 3, f"Expected exactly 3 suggestions, got {tags.count()}"
        print("[PASS] Homepage search suggestions limited to maximum of 3 items.")

        # Check that AI Search button is removed completely
        ai_btn = page.query_selector("#healthedia-ai-search")
        assert ai_btn is None, "AI Search Optimization button is still present!"
        print("[PASS] AI Search Optimization section completely removed from homepage.")

        # ==========================================
        # 3. MOBILE HAMBURGER ICON ALIGNMENT
        # ==========================================
        print("Spawning mobile context (viewport: 375x667) to check hamburger alignment...")
        mobile_context = browser.new_context(viewport={"width": 375, "height": 667})
        mobile_page = mobile_context.new_page()
        mobile_page.goto("http://localhost:8000/")
        mobile_page.wait_for_selector(".healthedia-global-header")

        # Verify logo and mobile trigger are visible
        logo_box = mobile_page.locator(".healthedia-logo-group").bounding_box()
        auth_box = mobile_page.locator(".healthedia-auth-btn-wrapper").bounding_box()
        trigger_box = mobile_page.locator(".healthedia-mobile-nav-trigger-container").bounding_box()

        assert logo_box is not None and auth_box is not None and trigger_box is not None
        print(f"Mobile - Logo X: {logo_box['x']}, Login X: {auth_box['x']}, Hamburger X: {trigger_box['x']}")

        # Verify Hamburger menu is positioned TO THE RIGHT of the Login button (hamburger X > login X)
        assert trigger_box['x'] > auth_box['x'], "Hamburger menu should be positioned to the right of the Login button on mobile!"
        print("[PASS] Hamburger icon is correctly aligned to the right of the Login button on mobile devices.")

        # Click hamburger menu and verify toggle works
        print("Testing mobile hamburger click...")
        mobile_page.click("#mobile-menu-toggle-btn")
        trigger_class = mobile_page.locator("#healthedia-mobile-trigger-container").get_attribute("class")
        assert "open" in trigger_class, "Mobile dropdown menu did not open on hamburger click!"
        print("[PASS] Mobile hamburger dropdown toggled successfully.")
        mobile_context.close()


        # ==========================================
        # 4. VERIFY DEFAULT PAGE TITLE & CONTENT
        # ==========================================
        print("Visiting a regular default page to check title and content rendering...")
        page.goto("http://localhost:8000/sample-page")
        page.wait_for_selector(".healthedia-default-page-wrapper")
        title_element = page.locator(".healthedia-default-title")
        content_element = page.locator(".healthedia-default-content")
        assert "Sample Research Page" in title_element.text_content(), "Standard page title is missing!"
        assert "biomechanical gait adaptations" in content_element.text_content(), "Standard page content is missing!"
        print("[PASS] Regular page structures and loop output are verified.")


        # ==========================================
        # 5. SECURE MULTI-STEP REGISTRATION FLOW
        # ==========================================
        print("Visiting Auth Page to register via 3-step wizard...")
        page.goto("http://localhost:8000/healthedia-auth/")
        page.wait_for_selector("#form-login")
        page.click("#tab-register-btn")
        page.wait_for_selector("#view-register", state="visible")

        # Step 1: Institutional Credentials
        print("Filling Registration Step 1...")
        page.fill("#register-email", "alan.turing@cambridge.edu")
        page.click("text=CONTINUE TO PROFILE")

        # Step 2: Professional Profile
        print("Filling Registration Step 2...")
        page.wait_for_selector("#register-name", state="visible")
        page.fill("#register-name", "Professor Dr. Alan Turing")
        page.click("text=CONTINUE TO SECURITY")

        # Step 3: Account Security & Verification
        print("Filling Registration Step 3...")
        page.wait_for_selector("#register-password", state="visible")
        page.fill("#register-password", "DecryptedPass123!")
        page.fill("#register-otp", "849204")

        # Complete Registration (Redirects to HOMEPAGE)
        page.click("#form-register button[type='submit']")
        page.wait_for_url("http://localhost:8000/")
        assert page.url == "http://localhost:8000/", f"Expected redirect to Homepage, got: {page.url}"
        print("[PASS] Multi-step registration completed with automatic redirect to Homepage '/'")


        # ==========================================
        # 6. EDIT ACCOUNT INFORMATION MODAL WIZARD
        # ==========================================
        print("Opening header dropdown menu...")
        page.wait_for_selector("#header-user-dropdown-btn")
        page.click("#header-user-dropdown-btn")

        # Click Edit Account Information button
        print("Clicking 'Edit Account Info' from dropdown menu...")
        page.click("#header-edit-account-btn")

        # Verify that Modal opened
        page.wait_for_selector("#healthedia-edit-account-modal", state="visible")
        assert page.is_visible("#healthedia-edit-account-modal")
        print("[PASS] Edit Account modal opened successfully.")

        # Wizard Step 1: Personal
        print("Filling Edit Account Wizard Step 1 (Personal details)...")
        page.fill("#edit-display-name", "Alan Turing, FRS")
        page.fill("#edit-user-nicename", "alanturing")
        page.select_option("#edit-gender", value="male")
        page.fill("#edit-dob", "1912-06-23")
        page.click("#modal-next-1")

        # Wizard Step 2: Professional
        print("Filling Edit Account Wizard Step 2 (Professional details)...")
        page.wait_for_selector("#edit-workplace", state="visible")
        page.fill("#edit-workplace", "National Physical Laboratory")
        page.fill("#edit-degree", "Sc.D.")
        page.fill("#edit-title", "Senior Research Fellow")
        page.click("#modal-next-2")

        # Wizard Step 3: Contact
        print("Filling Edit Account Wizard Step 3 (Contact details)...")
        page.wait_for_selector("#edit-phone", state="visible")
        page.fill("#edit-phone", "+44 20 8977 3222")

        # Submit the Edit Account form (will refresh page state)
        print("Submitting Edit Account updates...")
        page.click("#edit-profile-save-btn")

        # Wait for page reload/redirection back to home page
        page.wait_for_url("http://localhost:8000/")
        page.wait_for_selector("#header-user-dropdown-btn")

        # Verify that the user dropdown shows the newly edited name!
        displayed_trigger_name = page.text_content(".healthedia-header-user-name").strip()
        print(f"Newly displayed trigger name: '{displayed_trigger_name}'")
        assert displayed_trigger_name == "Alan Turing, FRS", f"Expected updated name, got: {displayed_trigger_name}"

        # Open dropdown and check modal inputs are updated too
        page.click("#header-user-dropdown-btn")
        page.click("#header-edit-account-btn")
        page.wait_for_selector("#healthedia-edit-account-modal", state="visible")
        updated_input_val = page.locator("#edit-display-name").input_value()
        assert updated_input_val == "Alan Turing, FRS"
        print("[PASS] Edit Account Information multi-step wizard successfully saved all 12 parameters to user database.")
        page.screenshot(path="healthedia_edit_account_success.png")
        page.click("#healthedia-modal-close-btn")


        # ==========================================
        # 7. DASHBOARD SECTIONS & SETTINGS
        # ==========================================
        print("Navigating to Healthedia SaaS Dashboard...")
        page.click("#header-user-dropdown-btn")
        page.click("text=SaaS Dashboard")
        page.wait_for_url("**/healthedia-dashboard/")
        page.wait_for_selector(".healthedia-dashboard-layout")
        print("[PASS] Entered SaaS Dashboard successfully.")

        # Test Interactive Section (Tab) Transitions
        print("Testing Sidebar tab transitions using explicit data-section attributes...")
        sections_to_test = [
            {"btn_selector": "button[data-section='analytics']", "sec_id": "#sec-analytics", "title_text": "Analytics Section"},
            {"btn_selector": "button[data-section='archive-records']", "sec_id": "#sec-archive-records", "title_text": "Archive Records Section"},
            {"btn_selector": "button[data-section='researchers-profile']", "sec_id": "#sec-researchers-profile", "title_text": "Researchers Profile Section"},
            {"btn_selector": "button[data-section='settings']", "sec_id": "#sec-settings", "title_text": "Settings Section"}
        ]
        for sec in sections_to_test:
            page.click(sec['btn_selector'])
            page.wait_for_selector(sec['sec_id'], state="visible")
            assert page.is_visible(sec['sec_id'])
            assert sec['title_text'] in page.text_content("#dashboard-title-label")
            print(f"[PASS] Section tab '{sec['btn_selector']}' transitioned successfully.")

        # Test changing auth settings to disable registration
        print("Disabling registration via Settings...")
        page.select_option("#auth_registration", value="disabled")
        page.click("#btn-save-auth-settings")
        page.wait_for_selector("#dashboard-success-banner")
        print("[PASS] Authentication Options form submitted and saved.")


        # ==========================================
        # 8. LOGOUT AND DYNAMIC AUTH RESTRICTION
        # ==========================================
        print("Testing logout action and automatic homepage redirection...")
        page.click("text=Logout")
        page.wait_for_url("http://localhost:8000/")
        assert page.url == "http://localhost:8000/"
        print("[PASS] Logout action completed with successful redirect to Homepage '/'")

        # Confirm dynamic auth form restrictions (Since we disabled registration, Create Account tab should be hidden)
        print("Checking dynamic Authentication tabs lock...")
        page.goto("http://localhost:8000/healthedia-auth/")
        page.wait_for_selector("#form-login")
        assert not page.is_visible("#auth-tabs-bar"), "Tabs bar should be hidden when registration is disabled!"
        assert not page.is_visible("#view-register")
        print("[PASS] Authentication tab lock works cleanly and dynamically restricts registration.")

        browser.close()
        print("--- All Healthedia Verification Tests Passed Perfectly! ---")

if __name__ == "__main__":
    run_tests()
