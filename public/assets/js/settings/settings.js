// Main entry point for the settings page
import { initAccountForm } from './account.js';
import { initSecurityTab } from './security.js';
import { initPreferencesTab } from './preferences.js';

// --- GLOBAL VARS ---
const BASE_URL = '/iCensus-ent/public';
const COOLDOWN_DURATION = 60; // Must match PHP

// --- Shared AJAX Result Modal ---
const ajaxModal = document.getElementById('ajaxResultModal');
const ajaxMessage = document.getElementById('ajaxResultMessage');
const ajaxModalContent = ajaxModal.querySelector('.modal-content');
const ajaxCloseBtn = ajaxModal.querySelector('.close');

ajaxCloseBtn.onclick = () => ajaxModal.style.display = "none";
window.onclick = (event) => { if (event.target === ajaxModal) ajaxModal.style.display = "none"; };

function showAjaxResult(message, type = 'success') {
    ajaxMessage.textContent = message;
    ajaxModalContent.className = 'modal-content ' + type;
    ajaxModal.style.display = 'block';
    setTimeout(() => { ajaxModal.style.display = "none"; }, 4000);
}

// --- Main DOMContentLoaded ---
document.addEventListener('DOMContentLoaded', () => {
    // --- Tab Functionality ---
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            tabPanes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(`tab-${button.dataset.tab}`).classList.add('active');
        });
    });

    // --- Initialize Modules ---
    // Pass the shared helper functions to each module
    const helpers = {
        showAjaxResult,
        BASE_URL,
        COOLDOWN_DURATION
    };

    initAccountForm(helpers);
    initSecurityTab(helpers);
    initPreferencesTab(helpers);
});