import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

/* ── Mobile navigation paper effect ─────────────────────────────── */
const mobileMenuButton = document.querySelector('button[aria-controls="mobile-navigation"]');
const mobileMenu = document.querySelector("#mobile-navigation");
let paperAnimationTimer;

const createPaperSheets = (button, menu) => {
    const buttonRect = button.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const sheetCount = 7;
    const sheetWidth = Math.max(10, Math.min(24, buttonRect.width * 0.55));
    const sheetHeight = Math.max(32, Math.min(56, menuRect.height * 0.72));
    const sheetGap = (menuRect.width - sheetWidth * sheetCount) / (sheetCount + 1);
    const startX = buttonRect.left + buttonRect.width / 2;
    const startY = buttonRect.top + buttonRect.height / 2;

    for (let index = 0; index < sheetCount; index += 1) {
        const sheet = document.createElement("div");
        const targetX = menuRect.left + sheetGap * (index + 1) + sheetWidth * (index + 0.5);
        const targetY = menuRect.top + menuRect.height * (0.48 + (index % 3) * 0.08);
        const rotation = (index - 3) * 24;

        sheet.className = "paper-sheet";
        sheet.setAttribute("aria-hidden", "true");
        sheet.style.left = `${startX}px`;
        sheet.style.top = `${startY}px`;
        sheet.style.width = `${sheetWidth}px`;
        sheet.style.height = `${sheetHeight}px`;
        sheet.style.opacity = "0";
        document.body.appendChild(sheet);

        const animation = sheet.animate(
            [
                { opacity: 0, transform: `translate(0, 0) rotate(0deg) scale(0.45)` },
                { opacity: 0.92, transform: `translate(${(targetX - startX) * 0.42}px, ${(targetY - startY) * 0.42}px) rotate(${rotation * 0.55}deg) scale(1)` },
                { opacity: 0.66, transform: `translate(${targetX - startX}px, ${targetY - startY}px) rotate(${rotation}deg) scale(1)` },
                { opacity: 0, transform: `translate(${targetX - startX + (index % 2 ? 28 : -28)}px, ${targetY - startY + 42}px) rotate(${rotation * 1.8}deg) scale(0.8)` },
            ],
            {
                duration: 760,
                easing: "cubic-bezier(0.16, 1, 0.3, 1)",
                fill: "forwards",
            },
        );

        animation.finished.then(() => sheet.remove()).catch(() => sheet.remove());
    }
};

const createClosingPaperSheets = (button, menu) => {
    const buttonRect = button.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const sheetCount = 7;

    for (let index = 0; index < sheetCount; index += 1) {
        const sheet = document.createElement("div");
        const targetX = buttonRect.left + buttonRect.width * (0.22 + index * 0.08);
        const targetY = buttonRect.top + buttonRect.height * (0.28 + (index % 3) * 0.2);
        const startX = menuRect.left + (menuRect.width / sheetCount) * index;
        const startY = menuRect.top + menuRect.height * (0.42 + (index % 3) * 0.08);

        sheet.className = "paper-sheet";
        sheet.setAttribute("aria-hidden", "true");
        sheet.style.left = `${startX}px`;
        sheet.style.top = `${startY}px`;
        sheet.style.width = `${Math.max(10, Math.min(24, buttonRect.width * 0.55))}px`;
        sheet.style.height = `${Math.max(32, Math.min(56, menuRect.height * 0.72))}px`;
        document.body.appendChild(sheet);

        const animation = sheet.animate(
            [
                { opacity: 0.72, transform: `translate(0, 0) rotate(0deg) scale(1)` },
                { opacity: 1, transform: `translate(${targetX - startX}px, ${targetY - startY}px) rotate(${(index - 3) * 14}deg) scale(0.96)` },
                { opacity: 0, transform: `translate(${targetX - startX + 12}px, ${targetY - startY + 18}px) rotate(${(index - 3) * 28}deg) scale(0.35)` },
            ],
            {
                duration: 540,
                easing: "cubic-bezier(0.55, 0, 1, 0.45)",
                fill: "forwards",
            },
        );

        animation.finished.then(() => sheet.remove()).catch(() => sheet.remove());
    }
};

if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener("click", () => {
        window.clearTimeout(paperAnimationTimer);

        const isOpen = mobileMenuButton.getAttribute("aria-expanded") === "true";
        if (isOpen) {
            createClosingPaperSheets(mobileMenuButton, mobileMenu);
        } else {
            createPaperSheets(mobileMenuButton, mobileMenu);
        }

        paperAnimationTimer = window.setTimeout(() => {
            document.querySelectorAll(".paper-sheet").forEach((sheet) => sheet.remove());
        }, 850);
    });
}

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
