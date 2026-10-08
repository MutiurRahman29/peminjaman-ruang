import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

/* ── Landing section nav ───────────────────────────────────────── */
const landingLinks = document.querySelectorAll(".landing-section-link");
const landingSections = [
    ...document.querySelectorAll("#hero, #tentang, #fitur, #cara-kerja, #siap-mulai"),
];

if (landingLinks.length > 0 && landingSections.length > 0) {
    const setActiveSection = () => {
        const scrollPosition = window.scrollY + 180;
        let activeId = "hero";

        landingSections.forEach((section) => {
            if (scrollPosition >= section.offsetTop) {
                activeId = section.id;
            }
        });

        landingLinks.forEach((link) => {
            const isActive = new URL(link.href).hash === `#${activeId}`;
            link.classList.toggle("nav-link-active", isActive);
            link.setAttribute("aria-current", isActive ? "page" : "false");
        });
    };

    setActiveSection();
    window.addEventListener("scroll", setActiveSection, { passive: true });
}

/* ── Global form submit loading state ──────────────────────────── */
document.addEventListener("submit", (event) => {
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!submitBtn || submitBtn.dataset.noLoading) {
        return;
    }

    submitBtn.disabled = true;
    submitBtn.setAttribute("aria-busy", "true");

    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Memproses...</span>
    `;

    /* Pulihkan jika browser kembali (back button / navigation) */
    window.addEventListener("pageshow", () => {
        submitBtn.disabled = false;
        submitBtn.setAttribute("aria-busy", "false");
        submitBtn.innerHTML = originalContent;
    }, { once: true });
});

/* ── aria-invalid untuk field error ────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
    /* Tandai field yang memiliki error message di bawahnya */
    document.querySelectorAll(".field-error-msg").forEach((msg) => {
        const fieldId = msg.dataset.for;
        if (!fieldId) { return; }
        const field = document.getElementById(fieldId);
        if (field) {
            field.setAttribute("aria-invalid", "true");
            field.setAttribute("aria-describedby", msg.id);
        }
    });
});
