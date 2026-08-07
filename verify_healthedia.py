import os
import sys
import time
import subprocess
from playwright.sync_api import sync_playwright

def run_tests():
    print("--- Starting Healthedia Verification Tests ---")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        # Use a stateful/clean context
        context = browser.new_context()
        page = context.new_page()

        # 1. Access the Mock WordPress home/search page
        print("Visiting Home/Search Page...")
        page.goto("http://localhost:8000/")
        page.wait_for_selector(".healthedia-search-title")
        assert "HEALTHEDIA" in page.text_content(".healthedia-search-title")
        print("[PASS] Search Page rendered correctly.")

        # Check for AI Sparkle Button and verify no dollar sign paths
        ai_btn = page.query_selector("#healthedia-ai-search")
        assert ai_btn is not None, "AI Search button not found!"
        # Check that the global header is visible
        header = page.query_selector(".healthedia-global-header")
        assert header is not None, "Global Header missing on Home Page!"
        print("[PASS] Global Header present on home page.")

        # 2. Click LOGIN button in Global Header to redirect to Auth Page
        print("Clicking Login in Header...")
        page.click(".healthedia-btn-login")
        page.wait_for_selector("#form-login")
        assert "LOGIN TO ARCHIVE" in page.content()
        print("[PASS] Redirected to Auth Page successfully.")

        # 3. Test multi-tab interface switching
        print("Testing Forgot Password link from Login view...")
        page.click("#goto-forgot")
        page.wait_for_selector("#view-forgot", state="visible")
        assert page.is_visible("#view-forgot")
        assert not page.is_visible("#view-login")
        print("[PASS] Forgot Password view is visible.")

        print("Switching back to Login view...")
        page.click("#back-to-login")
        page.wait_for_selector("#view-login", state="visible")
        assert page.is_visible("#view-login")

        print("Testing tab switching (Create Account)...")
        page.click("#tab-register-btn")
        page.wait_for_selector("#view-register", state="visible")
        assert page.is_visible("#view-register")
        assert not page.is_visible("#view-login")
        print("[PASS] Registration view is visible.")

        # 4. Perform registration
        print("Registering a new account...")
        page.fill("#register-name", "Professor Dr. Alan Turing")
        page.fill("#register-email", "alan.turing@cambridge.edu")
        page.fill("#register-password", "DecryptedPass123!")

        # Submit the registration form
        page.click("#form-register button[type='submit']")

        # It should process, auto-sign-on and redirect to the SaaS Dashboard
        print("Waiting for redirection to SaaS Dashboard...")
        page.wait_for_url("**/healthedia-dashboard/")

        # 5. Verify the SaaS Dashboard
        print("Verifying SaaS Dashboard content and authentication access...")
        print("Current URL:", page.url)
        # Check page body first
        try:
            page.wait_for_selector(".healthedia-dashboard-layout", timeout=5000)
        except Exception as e:
            print("Failed waiting for .healthedia-dashboard-layout. Page content:")
            print(page.content())
            raise e

        # Check that the custom display name is dynamic and matches registered user!
        print("SaaS Dashboard Body Content snippet:")
        user_section = page.query_selector(".healthedia-topbar-user")
        if user_section:
            print("healthedia-topbar-user content:", user_section.inner_html())
        else:
            print("healthedia-topbar-user NOT FOUND!")
            print("Full page content:")
            print(page.content())
        user_name_element = page.query_selector(".healthedia-user-name")
        assert user_name_element is not None, "User name element not found on dashboard!"
        displayed_name = user_name_element.text_content().strip()
        print(f"Displayed user name on dashboard: '{displayed_name}'")
        assert "Professor Dr. Alan Turing" in displayed_name, f"Expected display name to be 'Professor Dr. Alan Turing', got '{displayed_name}'"
        print("[PASS] SaaS Dashboard successfully reflects dynamic user details.")

        # Check Avatar Initials
        avatar_element = page.query_selector(".healthedia-user-avatar")
        assert avatar_element is not None, "Avatar not found!"
        displayed_initials = avatar_element.text_content().strip()
        print(f"Displayed avatar initials: '{displayed_initials}'")
        assert "PD" in displayed_initials or "PA" in displayed_initials, f"Expected correct initials, got '{displayed_initials}'"
        print("[PASS] Avatar initials correctly generated from name.")

        # 6. Verify absolute exclusion of global header/footer on Dashboard
        print("Verifying global header/footer exclusion on SaaS Dashboard...")
        global_header = page.query_selector(".healthedia-global-header")
        global_footer = page.query_selector(".healthedia-global-footer")
        assert global_header is None, "Global header should NOT exist on SaaS dashboard!"
        assert global_footer is None, "Global footer should NOT exist on SaaS dashboard!"
        print("[PASS] Exclusion of global header and footer on dashboard verified successfully.")

        # Capture a screenshot of the premium SaaS dashboard
        screenshot_path = "healthedia_dashboard_screenshot.png"
        page.screenshot(path=screenshot_path, full_page=True)
        print(f"[PASS] Screenshot saved to {screenshot_path}")

        # 7. Test logout flow
        print("Testing Logout action...")
        page.click("text=Logout")
        page.wait_for_url("**/")

        # Ensure we are logged out (Login button should be visible in global header again)
        page.wait_for_selector(".healthedia-btn-login")
        login_btn_text = page.text_content(".healthedia-btn-login")
        assert "LOGIN" in login_btn_text, f"Expected 'LOGIN' button, got: {login_btn_text}"
        print("[PASS] Successfully logged out and verified header state.")

        browser.close()

if __name__ == "__main__":
    run_tests()
