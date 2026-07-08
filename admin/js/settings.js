/**
 * Beplus Advanced Reviews For Woocommerce — Admin Settings
 * Handles tab switching and toggle interactions.
 */
(function () {
	'use strict';

	/**
	 * Activate a tab panel.
	 *
	 * @param {string} tabName
	 */
	function activateTab(tabName) {
		var tabs = document.querySelectorAll('.bparfw-settings__tab');
		var panels = document.querySelectorAll('.bparfw-settings__panel');

		tabs.forEach(function (tab) {
			var isActive = tab.getAttribute('data-bparfw-tab') === tabName;
			tab.classList.toggle('is-active', isActive);
		});

		panels.forEach(function (panel) {
			var isActive = panel.getAttribute('data-bparfw-panel') === tabName;
			panel.classList.toggle('is-active', isActive);
		});
	}

	/**
	 * Handle toggle switch change.
	 *
	 * @param {HTMLInputElement} input
	 */
	function handleToggle(input) {
		var toggleGroup = input.getAttribute('data-bparfw-toggle');
		var stateLabel = document.querySelector('[data-bparfw-state-label="' + toggleGroup + '"]');
		var offNote = document.querySelector('[data-bparfw-off-note="' + toggleGroup + '"]');
		var panel = document.querySelector('[data-bparfw-panel-section="' + toggleGroup + '"]');
		var isOn = input.checked;

		if (stateLabel) {
			stateLabel.textContent = isOn ? 'On' : 'Off';
		}
		if (offNote) {
			offNote.hidden = isOn;
		}
		if (panel) {
			panel.hidden = !isOn;
		}
	}

	// Tab click handler
	document.addEventListener('click', function (e) {
		var tab = e.target.closest('.bparfw-settings__tab');
		if (!tab) return;

		e.preventDefault();
		var tabName = tab.getAttribute('data-bparfw-tab');
		if (tabName) {
			activateTab(tabName);

			if (window.history && window.history.replaceState) {
				window.history.replaceState(null, '', '#tab-' + tabName);
			}
		}
	});

	// Toggle change handler
	document.addEventListener('change', function (e) {
		var input = e.target;
		if (input.matches && input.matches('.bparfw-toggle__input')) {
			handleToggle(input);
		}
	});

	// Restore active tab from hash on load
	document.addEventListener('DOMContentLoaded', function () {
		var hash = window.location.hash;
		if (hash && hash.indexOf('#tab-') === 0) {
			var tabName = hash.replace('#tab-', '');
			if (tabName) {
				activateTab(tabName);
			}
		}
	});
})();
