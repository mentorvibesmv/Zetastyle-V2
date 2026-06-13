/* Promotional carousel */
(function () {
    const slider = document.querySelector("[data-slider]");

    if (!slider) {
        return;
    }

    const track = slider.querySelector("[data-slider-track]");
    const slides = Array.from(track.children);
    const prev = slider.querySelector("[data-slider-prev]");
    const next = slider.querySelector("[data-slider-next]");
    const dotsWrap = slider.querySelector("[data-slider-dots]");
    let index = 0;
    let timer;

    function renderDots() {
        slides.forEach((slide, slideIndex) => {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.setAttribute("aria-label", `Go to promotion ${slideIndex + 1}`);
            dot.addEventListener("click", () => goTo(slideIndex));
            dotsWrap.appendChild(dot);
        });
    }

    function update() {
        track.style.transform = `translateX(-${index * 100}%)`;
        Array.from(dotsWrap.children).forEach((dot, dotIndex) => {
            dot.classList.toggle("active", dotIndex === index);
        });
    }

    function goTo(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;
        update();
        restart();
    }

    function restart() {
        window.clearInterval(timer);
        timer = window.setInterval(() => goTo(index + 1), 5200);
    }

    prev.addEventListener("click", () => goTo(index - 1));
    next.addEventListener("click", () => goTo(index + 1));
    renderDots();
    update();
    restart();
})();
