import './bootstrap';

const authOverlay = document.querySelector('[data-auth-overlay]');

if (authOverlay) {
	const dialog = authOverlay.querySelector('[role="dialog"]');
	const focusableElements = [...dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')];
	const firstFocusableElement = focusableElements[0];
	const lastFocusableElement = focusableElements[focusableElements.length - 1];
	const initialFocusElement = focusableElements.find((element) => element.hasAttribute('autofocus')) ?? firstFocusableElement;

	initialFocusElement?.focus();

	dialog.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			window.location.assign(authOverlay.dataset.closeUrl);
		}

		if (event.key !== 'Tab' || !firstFocusableElement || !lastFocusableElement) {
			return;
		}

		if (event.shiftKey && document.activeElement === firstFocusableElement) {
			event.preventDefault();
			lastFocusableElement.focus();
		} else if (!event.shiftKey && document.activeElement === lastFocusableElement) {
			event.preventDefault();
			firstFocusableElement.focus();
		}
	});
}

