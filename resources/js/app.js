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

document.querySelectorAll('[data-nav-toggle]').forEach((toggle) => {
	const target = document.querySelector(toggle.dataset.navTarget);

	if (!target) {
		return;
	}

	const setOpen = (open) => {
		target.classList.toggle('is-open', open);
		toggle.setAttribute('aria-expanded', String(open));
		toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
	};

	toggle.addEventListener('click', () => setOpen(!target.classList.contains('is-open')));

	target.querySelectorAll('a').forEach((link) => {
		link.addEventListener('click', () => setOpen(false));
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			setOpen(false);
		}
	});

	window.addEventListener('resize', () => {
		if (window.innerWidth > 800) {
			setOpen(false);
		}
	});
});

const quantityStepperInput = (element) => element?.closest?.('[data-qty-stepper]')?.querySelector('input[type="number"][name="quantity"]');

const syncQuantityStepper = (input) => {
	const stepper = input.closest('[data-qty-stepper]');
	const min = Number(input.min || 1);
	const max = Number(input.max || 99);
	const value = Number(input.value);

	stepper.querySelector('[data-qty-step="-1"]')?.toggleAttribute('disabled', value <= min);
	stepper.querySelector('[data-qty-step="1"]')?.toggleAttribute('disabled', value >= max);
};

document.querySelectorAll('[data-qty-stepper] input[type="number"][name="quantity"]').forEach(syncQuantityStepper);

document.addEventListener('click', (event) => {
	const control = event.target.closest?.('[data-qty-step]');

	if (!control) {
		return;
	}

	const input = quantityStepperInput(control);

	if (!input) {
		return;
	}

	event.preventDefault();

	const min = Number(input.min || 1);
	const max = Number(input.max || 99);
	const step = Number(control.dataset.qtyStep || 1);
	const current = Number.parseInt(input.value, 10);
	const next = (Number.isFinite(current) ? current : min) + step;

	input.value = String(Math.min(max, Math.max(min, next)));
	input.dispatchEvent(new Event('input', { bubbles: true }));
	syncQuantityStepper(input);
});

document.addEventListener('input', (event) => {
	const input = quantityStepperInput(event.target);

	if (input) {
		syncQuantityStepper(input);
	}
});

document.addEventListener('change', (event) => {
	const input = quantityStepperInput(event.target);

	if (!input) {
		return;
	}

	const min = Number(input.min || 1);
	const max = Number(input.max || 99);
	const current = Number.parseInt(input.value, 10);

	input.value = String(Number.isFinite(current) ? Math.min(max, Math.max(min, current)) : min);
	syncQuantityStepper(input);
});

