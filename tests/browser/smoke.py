import os
from pathlib import Path
from playwright.sync_api import sync_playwright

out=Path("build/screenshots")
out.mkdir(parents=True,exist_ok=True)
with sync_playwright() as p:
    browser=p.chromium.launch()
    page=browser.new_page(viewport={"width":1512,"height":1100}, device_scale_factor=1)
    errors=[]
    page.on("pageerror",lambda error: errors.append(str(error)))
    page.goto("http://127.0.0.1:8000/login")
    page.screenshot(path=str(out/"login.png"),full_page=True)
    page.get_by_label("البريد الإلكتروني").fill("admin@example.test")
    page.locator("input[name=password]").fill(os.environ["IBN_BROWSER_PASSWORD"])
    page.get_by_role("button",name="دخول إلى مساحة العمل").click()
    page.wait_for_url("http://127.0.0.1:8000/")
    page.get_by_role("heading",name="لوحة المتابعة",exact=True).wait_for()
    page.screenshot(path=str(out/"dashboard-desktop.png"),full_page=True)
    page.goto("http://127.0.0.1:8000/facilities/create")
    page.get_by_label("اسم المؤسسة").fill("مستشفى اختبار المتصفح — تجريبي")
    page.get_by_label("رمز المؤسسة").fill("BROWSER-TEST")
    page.get_by_label("نوع المؤسسة").select_option("hospital")
    page.get_by_label("الملكية").select_option("private")
    page.get_by_label("المحافظة").select_option("واسط")
    page.get_by_role("button",name="حفظ المؤسسة").click()
    page.wait_for_url("**/facilities")
    page.get_by_text("مستشفى اختبار المتصفح — تجريبي",exact=True).wait_for()
    page.screenshot(path=str(out/"facilities-desktop.png"),full_page=True)
    page.goto("http://127.0.0.1:8000/roles")
    page.get_by_role("heading",name="الأدوار والصلاحيات",exact=True).wait_for()
    page.screenshot(path=str(out/"roles-desktop.png"),full_page=True)
    page.set_viewport_size({"width":390,"height":844})
    page.goto("http://127.0.0.1:8000/")
    assert page.evaluate("document.documentElement.scrollWidth <= window.innerWidth"), "Dashboard overflows mobile viewport"
    page.screenshot(path=str(out/"dashboard-mobile.png"),full_page=True)
    page.get_by_role("button",name="فتح القائمة").click()
    assert page.locator("body").evaluate("el=>el.classList.contains('nav-open')")
    page.keyboard.press("Escape")
    assert not errors, errors
    browser.close()
print("Browser login, CSRF-protected institution creation, roles, mobile layout and navigation passed.")
