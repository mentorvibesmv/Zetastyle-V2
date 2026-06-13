/* Global UI behavior */
(function () {
    const header = document.querySelector("[data-header]");
    const revealItems = document.querySelectorAll(".reveal");
    const contactForm = document.querySelector("[data-contact-form]");

    function onScroll() {
        if (header) {
            header.classList.toggle("is-scrolled", window.scrollY > 12);
        }
    }

    function setupReveal() {
        if (!("IntersectionObserver" in window)) {
            revealItems.forEach((item) => item.classList.add("visible"));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.16 });

        revealItems.forEach((item) => observer.observe(item));
    }

    function showToast(message) {
        const toast = document.querySelector("[data-toast]");
        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add("show");
        window.clearTimeout(window.zetaToastTimer);
        window.zetaToastTimer = window.setTimeout(() => toast.classList.remove("show"), 2600);
    }

    function setupContactForm() {
        if (!contactForm) {
            return;
        }

        contactForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const status = contactForm.querySelector("[data-form-status]");

            if (!contactForm.checkValidity()) {
                contactForm.reportValidity();
                return;
            }

            status.textContent = "Sending...";

            try {
                const response = await fetch(contactForm.action, {
                    method: "POST",
                    body: new FormData(contactForm),
                    headers: { "Accept": "application/json" },
                });
                const data = await response.json();
                status.textContent = data.message;

                if (data.success) {
                    contactForm.reset();
                    showToast("Message sent to ZetaStyle");
                }
            } catch (error) {
                status.textContent = "Unable to send right now. Please try again.";
            }
        });
    }

    window.ZetaStyle = Object.assign(window.ZetaStyle || {}, { showToast });
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    setupReveal();
    setupContactForm();
})();
