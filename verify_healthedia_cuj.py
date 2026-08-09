import sys
import time
import os
from playwright.sync_api import sync_playwright

def run_cuj(page):
    print("Step 1: Visiting Homepage...")
    page.goto("http://localhost:8000")
    page.wait_for_timeout(500)

    # Make sure screenshot dir exists
    os.makedirs("verification_screenshots", exist_ok=True)

    # Take screenshot of Homepage
    page.screenshot(path="verification_screenshots/homepage.png")
    page.wait_for_timeout(500)

    print("Step 2: Visiting Auth Page (Login)...")
    page.goto("http://localhost:8000/login/")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/login_page.png")

    print("Step 3: Clicking 'Create Account' tab...")
    page.click("#tab-register-btn")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/register_step_1.png")

    print("Step 4: Filling Register Step 1...")
    dynamic_email = f"cuj_user_{int(time.time())}@oxford.edu"
    page.fill("#register-email", dynamic_email)
    page.select_option("#register-institution", value="Oxford Health Sciences")
    page.wait_for_timeout(500)
    page.click("text=CONTINUE TO PHOTO")
    page.wait_for_timeout(500)

    print("Step 5: Uploading Profile Pic on Step 2...")
    page.set_input_files("#register-profile-pic", "dummy_avatar.png")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/register_step_2.png")
    page.click("text=CONTINUE TO PROFILE")
    page.wait_for_timeout(500)

    print("Step 6: Filling Register Step 3...")
    page.fill("#register-first-name", "Elizabeth")
    page.fill("#register-last-name", "Blackwell")
    page.select_option("#register-specialty", value="Cardiovascular Science")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/register_step_3.png")
    page.click("text=CONTINUE TO SECURITY")
    page.wait_for_timeout(500)

    print("Step 7: Filling Register Step 4...")
    page.fill("#register-password", "ElizabethPass123!")
    page.fill("#register-confirm-password", "ElizabethPass123!")

    # Send OTP to automatically populate the 849204 OTP code
    page.click("text=SEND OTP")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/register_step_4.png")

    # Complete Registration (Redirects to Homepage)
    page.click("#form-register button[type='submit']")
    page.wait_for_url("http://localhost:8000/")
    page.wait_for_timeout(1000)
    page.screenshot(path="verification_screenshots/logged_in_homepage.png")

    print("Step 8: Opening Edit Account Info Modal...")
    page.click("#header-user-dropdown-btn")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/header_dropdown.png")
    page.click("#header-edit-account-btn")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/edit_modal_step_1.png")

    print("Step 9: Filling Edit Account Step 1...")
    page.fill("#edit-first-name", "Liz")
    page.fill("#edit-last-name", "Blackwell")
    page.click("#modal-next-1")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/edit_modal_step_2.png")

    print("Step 10: Uploading Profile Photo on Step 2...")
    page.set_input_files("#edit-profile-pic", "dummy_avatar.png")
    page.click("#modal-next-2")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/edit_modal_step_3.png")

    print("Step 11: Filling Step 3 (Contact)...")
    page.fill("#edit-phone", "+44 7700 900077")
    page.click("#modal-next-3")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/edit_modal_step_4.png")

    print("Step 12: Filling Step 4 (Credentials) & Submitting...")
    page.fill("#edit-workplace", "Oxford Medical College")
    page.fill("#edit-degree", "M.D.")
    page.fill("#edit-title", "Lead Physician")
    page.fill("#edit-nationality", "British-American")
    page.fill("#edit-country", "United Kingdom")
    page.wait_for_timeout(500)
    page.click("#edit-profile-save-btn")

    page.wait_for_url("http://localhost:8000/")
    page.wait_for_timeout(1000)

    # Confirm name display in dropdown trigger
    print("Step 13: Verifying newly saved name in Header trigger...")
    page.click("#header-user-dropdown-btn")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/header_updated_trigger.png")

    # Navigate to SaaS Dashboard
    print("Step 14: Navigating to SaaS Dashboard...")
    page.click("text=SaaS Dashboard")
    page.wait_for_url("**/healthedia-dashboard/")
    page.wait_for_timeout(1000)
    page.screenshot(path="verification_screenshots/saas_dashboard.png")

    # Go to verification section
    print("Step 15: Creating and viewing a verification record...")
    page.click("button[data-section='verification-admin']")
    page.wait_for_selector("#sec-verification-admin", state="visible")
    page.wait_for_timeout(500)
    page.fill("#new_serial", "CUJ-VERIFY-777")
    page.fill("#new_recipient", "Liz Blackwell")
    page.fill("#new_doc_type", "M.D. Degree")
    page.fill("#new_date_issued", "1849-01-23")
    page.select_option("#new_status", value="Active")
    page.fill("#new_notes", "Verified dynamically via Playwright script.")
    page.click("#verification-add-submit-btn")
    page.wait_for_timeout(500)
    page.screenshot(path="verification_screenshots/saas_dashboard_verification.png")

    # Go to public Verification Portal
    print("Step 16: Querying in public verification portal...")
    page.goto("http://localhost:8000/verification/")
    page.wait_for_timeout(500)
    page.fill("#verification-serial-input", "CUJ-VERIFY-777")
    page.click("#verification-search-btn")
    page.wait_for_timeout(1000)

    # Capture final visual verification page screenshot!
    page.screenshot(path="verification_screenshots/verification_success.png")
    print("CUJ completed successfully!")

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        # Record video into verification_screenshots directory
        context = browser.new_context(
            record_video_dir="verification_screenshots"
        )
        page = context.new_page()
        try:
            run_cuj(page)
        finally:
            context.close()
            browser.close()
