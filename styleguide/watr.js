// watr.js: optional helpers for watr.css. no dependencies.
//
//   dialogs:  <button data-watr-open="rename">   opens <dialog id="rename" class="watr-dialog">
//             <button data-watr-close>            closes the dialog it's in
//             clicking the dimmed backdrop also closes it
//   toasts:   watr.toast("Uploaded backup.zip", { type: "info", action: "undo", onAction: fn, time: 4000 })
//             type is "info" (default), "plain", "warning" or "danger". time 0 = stays until closed
//   sidebar:  <button data-watr-sidebar="sidebar"> toggles .is-hidden on #sidebar
//   tabs:     clicking a [role=tab] inside .watr-tabs selects it
//   tables:   a <button> in a .watr-table header cell sorts by that column. put a raw value in
//             data-sort on cells that don't sort by their text (sizes, dates)
//             a checkbox in a row marks the row .is-selected, one in the header selects every row

(function () {
    function toastRegion() {
        let region = document.querySelector(".watr-toasts:not([data-watr-static])");
        if (!region) {
            region = document.createElement("div");
            region.className = "watr-toasts";
            region.setAttribute("aria-live", "polite");
            document.body.appendChild(region);
        }
        return region;
    }

    function dismiss(toast) {
        if (toast.classList.contains("is-leaving")) return;
        toast.classList.add("is-leaving");
        setTimeout(function () { toast.remove(); }, 250);
    }

    function toast(message, opts) {
        opts = opts || {};
        const type = opts.type || "info";
        const el = document.createElement("div");
        el.className = "watr-toast" + (type === "info" ? "" : " watr-toast--" + type);
        el.setAttribute("role", type === "danger" ? "alert" : "status");

        const text = document.createElement("p");
        text.textContent = message;
        el.appendChild(text);

        if (opts.action) {
            const action = document.createElement("button");
            action.type = "button";
            action.className = "watr-toast__action";
            action.textContent = opts.action;
            action.addEventListener("click", function () {
                if (opts.onAction) opts.onAction();
                dismiss(el);
            });
            el.appendChild(action);
        }

        const close = document.createElement("button");
        close.type = "button";
        close.className = "watr-toast__close";
        close.setAttribute("aria-label", "dismiss");
        close.textContent = "×";
        close.addEventListener("click", function () { dismiss(el); });
        el.appendChild(close);

        const time = opts.time === undefined ? 4000 : opts.time;
        if (time > 0) {
            // the shrinking bar is the timer: hovering pauses it, and the toast leaves when it runs out
            const timer = document.createElement("i");
            timer.className = "watr-toast__timer";
            timer.style.animationDuration = time + "ms";
            timer.addEventListener("animationend", function () { dismiss(el); });
            el.appendChild(timer);
        }

        toastRegion().appendChild(el);
        return el;
    }

    function sortTable(th) {
        const table = th.closest("table");
        const index = Array.prototype.indexOf.call(th.parentElement.children, th);
        const dir = th.getAttribute("aria-sort") === "ascending" ? "descending" : "ascending";
        table.querySelectorAll("thead th[aria-sort]").forEach(function (h) { h.removeAttribute("aria-sort"); });
        th.setAttribute("aria-sort", dir);

        const body = table.tBodies[0];
        const rows = Array.from(body.rows);
        const key = function (row) {
            const cell = row.cells[index];
            return cell.dataset.sort !== undefined ? cell.dataset.sort : cell.textContent.trim().toLowerCase();
        };
        const numeric = rows.every(function (r) { return key(r) !== "" && !isNaN(key(r)); });
        rows.sort(function (a, b) {
            const d = numeric ? key(a) - key(b) : key(a).localeCompare(key(b));
            return dir === "ascending" ? d : -d;
        });
        rows.forEach(function (r) { body.appendChild(r); });
    }

    document.addEventListener("change", function (e) {
        if (!e.target.matches(".watr-table input[type=checkbox]")) return;
        const table = e.target.closest("table");
        if (e.target.closest("thead")) {
            table.querySelectorAll("tbody input[type=checkbox]").forEach(function (box) {
                box.checked = e.target.checked;
                box.closest("tr").classList.toggle("is-selected", box.checked);
            });
        } else {
            e.target.closest("tr").classList.toggle("is-selected", e.target.checked);
        }
    });

    document.addEventListener("click", function (e) {
        const opener = e.target.closest("[data-watr-open]");
        if (opener) {
            const dialog = document.getElementById(opener.dataset.watrOpen);
            if (dialog && dialog.showModal) {
                dialog.returnValue = "";
                dialog.showModal();
            }
            return;
        }

        const closer = e.target.closest("[data-watr-close]");
        if (closer) {
            const dialog = closer.closest("dialog");
            if (dialog) dialog.close(closer.dataset.watrClose || "");
            return;
        }

        // a click that lands on the dialog element itself is a click on the backdrop
        if (e.target.matches("dialog.watr-dialog")) {
            e.target.close("");
            return;
        }

        const sideToggle = e.target.closest("[data-watr-sidebar]");
        if (sideToggle) {
            const sidebar = document.getElementById(sideToggle.dataset.watrSidebar);
            if (sidebar) sidebar.classList.toggle("is-hidden");
            return;
        }

        const tab = e.target.closest(".watr-tabs [role=tab]");
        if (tab) {
            tab.parentElement.querySelectorAll("[role=tab]").forEach(function (t) {
                t.setAttribute("aria-selected", t === tab ? "true" : "false");
            });
            return;
        }

        const sortButton = e.target.closest(".watr-table th button");
        if (sortButton) sortTable(sortButton.closest("th"));
    });

    window.watr = { toast: toast, dismiss: dismiss };
})();
