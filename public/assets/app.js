"use strict";
const toggle = document.querySelector("[data-menu-toggle]");
function closeMenu() { document.body.classList.remove("nav-open"); toggle?.setAttribute("aria-expanded", "false"); }
toggle?.addEventListener("click", () => { const open=document.body.classList.toggle("nav-open"); toggle.setAttribute("aria-expanded",String(open)); });
document.querySelector("[data-menu-close]")?.addEventListener("click",closeMenu);
document.addEventListener("keydown",event=>{if(event.key==="Escape") closeMenu();});
document.querySelectorAll("form[data-confirm]").forEach(form=>form.addEventListener("submit",event=>{if(!window.confirm(form.dataset.confirm)) event.preventDefault();}));
const roleSelect=document.querySelector("[data-role-selector]");
function updateFacility() {
 const wrapper=document.querySelector("[data-facility-field]"); if(!wrapper || !roleSelect) return;
 const national=roleSelect.selectedOptions[0]?.dataset.scope==="national";
 wrapper.hidden=national; const select=wrapper.querySelector("select"); select.disabled=national; select.required=!national;
}
roleSelect?.addEventListener("change",updateFacility); updateFacility();
