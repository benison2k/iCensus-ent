// public/assets/js/settings/settings.js
import { initAccountForm } from './account.js';
import { initSecurityTab } from './security.js';
import { initPreferencesTab } from './preferences.js';

// --- GLOBAL VARS ---
const BASE_URL = '/iCensus-ent/public';
const COOLDOWN_DURATION = 60; 

// --- Shared AJAX Result Modal ---
const ajaxModal = document.getElementById('ajaxResultModal');
const ajaxMessage = document.getElementById('ajaxResultMessage');
const ajaxModalContent = ajaxModal ? ajaxModal.querySelector('.modal-content') : null;
const ajaxCloseBtn = ajaxModal ? ajaxModal.querySelector('.close') : null;

// Safety check for modal elements
if (ajaxCloseBtn) {
    ajaxCloseBtn.onclick = () => ajaxModal.style.display = "none";
}
if (ajaxModal) {
    window.onclick = (event) => { if (event.target === ajaxModal) ajaxModal.style.display = "none"; };
}

function showAjaxResult(message, type = 'success') {
    if (!ajaxModal || !ajaxMessage || !ajaxModalContent) return;
    ajaxMessage.textContent = message;
    ajaxModalContent.className = 'modal-content ' + type;
    ajaxModal.style.display = 'block';
    setTimeout(() => { ajaxModal.style.display = "none"; }, 4000);
}

// --- Main DOMContentLoaded ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Tab Functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            tabPanes.forEach(pane => pane.classList.remove('active'));
            const targetId = `tab-${button.dataset.tab}`;
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // 2. Initialize Modules (With Error Handling)
    const helpers = { showAjaxResult, BASE_URL, COOLDOWN_DURATION };

    // We wrap each init in a try-catch so one failure doesn't kill the others
    try {
        console.log("Initializing Account Tab...");
        initAccountForm(helpers);
    } catch (error) {
        console.warn("Account Tab Init Failed:", error);
    }

    try {
        console.log("Initializing Security Tab...");
        initSecurityTab(helpers);
    } catch (error) {
        console.warn("Security Tab Init Failed:", error);
    }

    try {
        console.log("Initializing Preferences Tab...");
        initPreferencesTab(helpers); // <--- This runs your Sidebar logic
        console.log("Preferences Tab Initialized Successfully");
    } catch (error) {
        console.error("Preferences Tab Init Failed:", error);
    }
});