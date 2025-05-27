document.addEventListener("DOMContentLoaded", function () {
    // Fade-in effect for the entire page
    document.body.classList.add("loaded");

    // Function to reveal the hero image on scroll
    function revealHeroImage() {
        let heroImage = document.querySelector(".hero-image");
        if (heroImage) {
            let position = heroImage.getBoundingClientRect().top;
            let windowHeight = window.innerHeight;
            if (position < windowHeight - 100) {
                heroImage.classList.add("show");
            }
        }
    }

    // Run the function on scroll and on page load
    window.addEventListener("scroll", revealHeroImage);
    revealHeroImage(); // Check once when the page loads
});

// Fade-out effect when leaving the page
window.addEventListener("beforeunload", function () {
    document.body.style.opacity = "0";
});