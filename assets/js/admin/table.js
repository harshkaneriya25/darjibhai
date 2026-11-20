document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-table-search]").forEach(function (input) {
        const table = document.getElementById(input.getAttribute("data-table-search"));
        if (!table) return;

        const body = table.tBodies[0];
        if (!body) return;

        // Rows with a colspan cell are table empty-state placeholders, not records.
        const rows = Array.from(body.rows).filter(function (row) {
            return !row.querySelector("td[colspan]");
        });

        const wrapper = document.createElement("div");
        wrapper.className = "admin-table-pagination";

        const summary = document.createElement("span");
        summary.setAttribute("aria-live", "polite");

        const tools = document.createElement("div");
        tools.className = "admin-pagination-tools";

        const pageSizeLabel = document.createElement("label");
        pageSizeLabel.className = "admin-pagination-size";
        pageSizeLabel.append("Rows per page");

        const pageSize = document.createElement("select");
        pageSize.className = "admin-pagination-select";
        pageSize.setAttribute("aria-label", "Rows per page");
        [10, 25, 50].forEach(function (size) {
            const option = document.createElement("option");
            option.value = size;
            option.textContent = size;
            pageSize.appendChild(option);
        });
        pageSizeLabel.appendChild(pageSize);

        const navigation = document.createElement("div");
        navigation.className = "admin-pagination-nav";

        const currentPageLabel = document.createElement("span");
        currentPageLabel.className = "admin-pagination-page";

        function makeButton(label, accessibleName, move) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "admin-pagination-button";
            button.textContent = label;
            button.setAttribute("aria-label", accessibleName);
            button.addEventListener("click", function () {
                move();
                render();
            });
            return button;
        }

        let currentPage = 1;
        const previousButton = makeButton("Previous", "Previous page", function () { currentPage--; });
        const nextButton = makeButton("Next", "Next page", function () { currentPage++; });
        navigation.append(previousButton, currentPageLabel, nextButton);
        tools.append(pageSizeLabel, navigation);
        wrapper.append(summary, tools);

        const tableContainer = table.closest(".table-responsive") || table.parentElement;
        tableContainer.insertAdjacentElement("afterend", wrapper);

        function render() {
            const query = input.value.trim().toLocaleLowerCase();
            const filteredRows = rows.filter(function (row) {
                return row.textContent.toLocaleLowerCase().includes(query);
            });
            const size = Number(pageSize.value);
            const pageCount = Math.max(1, Math.ceil(filteredRows.length / size));
            currentPage = Math.min(Math.max(currentPage, 1), pageCount);

            rows.forEach(function (row) { row.style.display = "none"; });
            const startIndex = (currentPage - 1) * size;
            filteredRows.slice(startIndex, startIndex + size).forEach(function (row) {
                row.style.display = "";
            });

            if (filteredRows.length === 0) {
                summary.textContent = query ? "No matching records" : "No records";
            } else {
                summary.textContent = "Showing " + (startIndex + 1) + "–" + Math.min(startIndex + size, filteredRows.length) + " of " + filteredRows.length;
            }

            currentPageLabel.textContent = currentPage + " / " + pageCount;
            previousButton.disabled = currentPage <= 1;
            nextButton.disabled = currentPage >= pageCount;
        }

        input.addEventListener("input", function () {
            currentPage = 1;
            render();
        });
        pageSize.addEventListener("change", function () {
            currentPage = 1;
            render();
        });
        render();
    });
});
