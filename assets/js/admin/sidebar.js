document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");

    const overlay = document.getElementById(
        "sidebarOverlay"
    );

    const mobileMenuBtn = document.getElementById(
        "mobileMenuBtn"
    );


    if (
        !sidebar ||
        !overlay ||
        !mobileMenuBtn
    ) {
        return;
    }


    mobileMenuBtn.addEventListener(
        "click",
        function () {

            sidebar.classList.toggle("show");

            overlay.classList.toggle("show");

        }
    );


    overlay.addEventListener(
        "click",
        function () {

            sidebar.classList.remove("show");

            overlay.classList.remove("show");

        }
    );


    /*
     * Close sidebar after clicking menu
     * on mobile.
     */

    const menuItems = sidebar.querySelectorAll(
        ".menu-item"
    );


    menuItems.forEach(function (item) {

        item.addEventListener(
            "click",
            function () {

                if (
                    window.innerWidth <= 991
                ) {

                    sidebar.classList.remove(
                        "show"
                    );

                    overlay.classList.remove(
                        "show"
                    );

                }

            }
        );

    });

});