document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");

    if (mobileMenuBtn && sidebar && overlay) {

        mobileMenuBtn.addEventListener("click", function () {

            sidebar.classList.toggle("show");
            overlay.classList.toggle("show");

        });

        overlay.addEventListener("click", function () {

            sidebar.classList.remove("show");
            overlay.classList.remove("show");

        });

    }

});