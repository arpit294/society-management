// --- Migrated Blade Scripts ---

// 1. Settings page logic
const applyPenaltyToggle = document.getElementById("apply_penalty");
const penaltyTypeSelect = document.getElementById("penalty_type");
const discountTypeSelect = document.getElementById("discount_type");
const applyDiscountToggle = document.getElementById("apply_discount");

function getPageCurrencyCode(sourceEl = null) {
    return (
        sourceEl?.dataset?.currency ||
        document.getElementById("dashboard-chart-data")?.dataset?.currency ||
        document.getElementById("payments-chart-data")?.dataset?.currency ||
        document.getElementById("currency_select")?.value ||
        "INR"
    );
}

function getPageCurrencySymbol() {
    return (
        document
            .querySelector(".currency-symbol-preview")
            ?.textContent?.trim() ||
        document.getElementById("dashboard-chart-data")?.dataset
            ?.currencySymbol ||
        document.getElementById("payments-chart-data")?.dataset
            ?.currencySymbol ||
        (getPageCurrencyCode() === "USD" ? "$" : "\u20B9")
    );
}

function formatPageCurrency(value, sourceEl = null) {
    return new Intl.NumberFormat(
        getPageCurrencyCode(sourceEl) === "USD" ? "en-US" : "en-IN",
        {
            style: "currency",
            currency: getPageCurrencyCode(sourceEl),
        },
    ).format(value);
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
}

function parseJsonData(value, fallback = {}) {
    if (!value) return fallback;
    try {
        return JSON.parse(value);
    } catch (error) {
        console.error("Invalid JSON data attribute", error);
        return fallback;
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (!sidebar || !window.coreui?.Sidebar) return;
    const instance = window.coreui.Sidebar.getInstance(sidebar);
    instance?.toggle();
}

function toggleResidentOwnerSection(select) {
    const target = select.dataset.ownerSection;
    if (!target) return;
    document.querySelector(target)?.classList.toggle("d-none", select.value !== "rental");
}

document.addEventListener("DOMContentLoaded", function () {
    const settingsData = document.getElementById("settings-data");
    if (settingsData) {
        window.SMP_SETTINGS_CONFIG = window.SMP_SETTINGS_CONFIG || {};
        if (settingsData.dataset.availableCurrencies) {
            window.SMP_SETTINGS_CONFIG.availableCurrencies = parseJsonData(settingsData.dataset.availableCurrencies);
        }
        if (settingsData.dataset.currencySymbol) {
            window.SMP_SETTINGS_CONFIG.currencySymbol = settingsData.dataset.currencySymbol;
        }
        if (settingsData.dataset.societyLat) {
            window.SMP_SETTINGS_CONFIG.societyLat = parseFloat(settingsData.dataset.societyLat);
        }
        if (settingsData.dataset.societyLng) {
            window.SMP_SETTINGS_CONFIG.societyLng = parseFloat(settingsData.dataset.societyLng);
        }
        if (!window.SMP_SETTINGS_CONFIG.csrfToken) {
            window.SMP_SETTINGS_CONFIG.csrfToken = getCsrfToken();
        }
        if (settingsData.dataset.routes) {
            window.SMP_SETTINGS_CONFIG.routes = parseJsonData(settingsData.dataset.routes);
        }
    }

    document.querySelectorAll(".js-resident-type-toggle").forEach((select) => {
        toggleResidentOwnerSection(select);
    });

    const debuggerSwitch = document.getElementById("enable_debugger");
    if (debuggerSwitch && window.SMP_SETTINGS_CONFIG?.routes?.settingsStore) {
        debuggerSwitch.addEventListener("change", function () {
            const val = this.checked ? "1" : "0";
            if (val === "0") {
                const bar = document.querySelector(".phpdebugbar");
                if (bar) bar.style.display = "none";
            }

            fetch(window.SMP_SETTINGS_CONFIG.routes.settingsStore, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: JSON.stringify({ enable_debugger: val }),
            }).then((response) => {
                if (response.ok) {
                    window.location.reload();
                }
            });
        });
    }
});

document.addEventListener("click", function (event) {
    if (event.target.closest(".js-sidebar-toggle")) {
        toggleSidebar();
        return;
    }

    if (event.target.closest(".js-reload-page")) {
        window.location.reload();
        return;
    }

    const exportHideButton = event.target.closest(".js-hide-export-resident-modal");
    if (exportHideButton) {
        window.setTimeout(() => {
            const modal = document.getElementById("export-resident-modal");
            if (!modal || !window.coreui?.Modal) return;
            window.coreui.Modal.getInstance(modal)?.hide();
        }, 500);
    }

    const roleCard = event.target.closest(".role-card");
    if (roleCard && typeof window.selectRole === "function") {
        window.selectRole(roleCard, event);
    }
});

document.addEventListener("change", function (event) {
    if (event.target.matches(".js-auto-submit")) {
        event.target.form?.submit();
    }

    if (event.target.matches(".js-resident-type-toggle")) {
        toggleResidentOwnerSection(event.target);
    }
});

document.addEventListener("submit", function (event) {
    if (event.target.matches(".js-prevent-submit")) {
        event.preventDefault();
    }
});

function togglePenaltyFields() {
    const isChecked = applyPenaltyToggle ? applyPenaltyToggle.checked : false;
    document.querySelectorAll('input[name^="penalty_"]').forEach((input) => {
        if (input) input.disabled = !isChecked;
    });
    if (penaltyTypeSelect) penaltyTypeSelect.disabled = !isChecked;
}

function toggleDiscountFields() {
    const isChecked = applyDiscountToggle ? applyDiscountToggle.checked : false;
    document.querySelectorAll('input[name^="discount_"]').forEach((input) => {
        if (input) input.disabled = !isChecked;
    });
    if (discountTypeSelect) discountTypeSelect.disabled = !isChecked;
}

function updatePenaltyLabels() {
    if (!penaltyTypeSelect) return;
    const isFixed = penaltyTypeSelect.value === "fixed";
    const suffix = isFixed ? getPageCurrencySymbol() : "%";
    document.querySelectorAll(".penalty-suffix").forEach((el) => {
        el.innerText = suffix;
    });
}

function updateDiscountLabels() {
    if (!discountTypeSelect) return;
    const isFixed = discountTypeSelect.value === "fixed";
    const suffix = isFixed ? getPageCurrencySymbol() : "%";
    document.querySelectorAll(".discount-suffix").forEach((el) => {
        el.innerText = suffix;
    });
}

if (applyPenaltyToggle) {
    applyPenaltyToggle.addEventListener("change", togglePenaltyFields);
    togglePenaltyFields();
}
if (penaltyTypeSelect) {
    penaltyTypeSelect.addEventListener("change", updatePenaltyLabels);
    updatePenaltyLabels();
}
if (applyDiscountToggle) {
    applyDiscountToggle.addEventListener("change", toggleDiscountFields);
    toggleDiscountFields();
}
if (discountTypeSelect) {
    discountTypeSelect.addEventListener("change", updateDiscountLabels);
    updateDiscountLabels();
}

document.addEventListener("DOMContentLoaded", function () {
    const sidebarNav = document.querySelector("#sidebar .sidebar-nav");
    if (!sidebarNav) return;

    const storageKey = "smp.sidebar.scrollTop";
    const getScrollElement = () =>
        sidebarNav.querySelector(".simplebar-content-wrapper") || sidebarNav;

    const restoreSidebarScroll = () => {
        const savedScrollTop = sessionStorage.getItem(storageKey);
        if (savedScrollTop === null) return;

        getScrollElement().scrollTop = Number(savedScrollTop) || 0;
        sessionStorage.removeItem(storageKey);
    };

    sidebarNav.addEventListener("click", function (event) {
        const link = event.target.closest(".nav-link");
        if (!link || link.classList.contains("nav-group-toggle")) return;

        const href = link.getAttribute("href") || "";
        if (!href || href === "#") return;

        sessionStorage.setItem(storageKey, String(getScrollElement().scrollTop));
    });

    requestAnimationFrame(restoreSidebarScroll);
    window.setTimeout(restoreSidebarScroll, 100);
});

$(document).on("click", ".toggle-password-btn, .toggle-password", function () {
    const container = $(this).closest(".input-group, .position-relative, div");
    const input = container.find("input")[0];
    if (!input) return;
    if (input.type === "password") {
        input.type = "text";
        $(this)
            .attr("aria-label", "Hide password")
            .attr("data-coreui-original-title", "Hide password");
        $(this).find(".fa-eye").removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
        input.type = "password";
        $(this)
            .attr("aria-label", "Show password")
            .attr("data-coreui-original-title", "Show password");
        $(this).find(".fa-eye-slash").removeClass("fa-eye-slash").addClass("fa-eye");
    }
});

$(document).on("submit", "#resetPasswordForm", function (e) {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("new-password");
    const confirmPasswordInput = document.getElementById("confirm-password");

    const emailError = document.getElementById("js-email-error");
    const passwordError = document.getElementById("js-password-error");
    const confirmPasswordError = document.getElementById(
        "js-confirm-password-error",
    );

    let isValid = true;

    [emailInput, passwordInput, confirmPasswordInput].forEach((input) => {
        if (!input) return;
        input.classList.remove("is-invalid");
        const bladeError = input.parentElement.querySelector(
            '.invalid-feedback:not([id^="js-"])',
        );
        if (bladeError) bladeError.style.display = "none";
    });

    [emailError, passwordError, confirmPasswordError].forEach((err) => {
        if (err) {
            err.style.display = "none";
            err.textContent = "";
        }
    });

    if (emailInput) {
        if (!emailInput.value.trim()) {
            isValid = false;
            emailInput.classList.add("is-invalid");
            emailError.textContent = "The email field is required.";
            emailError.style.display = "block";
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            isValid = false;
            emailInput.classList.add("is-invalid");
            emailError.textContent = "Please enter a valid email address.";
            emailError.style.display = "block";
        }
    }

    if (passwordInput) {
        if (!passwordInput.value) {
            isValid = false;
            passwordInput.classList.add("is-invalid");
            passwordError.textContent = "The password field is required.";
            passwordError.style.display = "block";
        } else if (passwordInput.value.length < 5) {
            isValid = false;
            passwordInput.classList.add("is-invalid");
            passwordError.textContent =
                "The password must be at least 5 characters.";
            passwordError.style.display = "block";
        }

        if (
            confirmPasswordInput &&
            passwordInput.value &&
            passwordInput.value.length >= 5
        ) {
            if (!confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add("is-invalid");
                confirmPasswordError.textContent =
                    "Please confirm your password.";
                confirmPasswordError.style.display = "block";
            } else if (passwordInput.value !== confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add("is-invalid");
                confirmPasswordError.textContent =
                    "The password confirmation does not match.";
                confirmPasswordError.style.display = "block";
            }
        }
    }

    if (!isValid) e.preventDefault();
});

// 3. Chart Initializations
document.addEventListener("DOMContentLoaded", function () {
    const paymentsChartDataEl = document.getElementById("payments-chart-data");
    if (paymentsChartDataEl && document.getElementById("paymentsChart")) {
        const months = JSON.parse(
            paymentsChartDataEl.getAttribute("data-months"),
        );
        const revenueData = JSON.parse(
            paymentsChartDataEl.getAttribute("data-revenue"),
        );
        const ctx = document.getElementById("paymentsChart").getContext("2d");

        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, "rgba(46, 184, 92, 0.5)"); // Green
        gradient.addColorStop(1, "rgba(46, 184, 92, 0.0)");

        new Chart(ctx, {
            type: "line",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Monthly Collections",
                        backgroundColor: gradient,
                        borderColor: "#2eb85c",
                        borderWidth: 3,
                        pointBackgroundColor: "#2eb85c",
                        pointBorderColor: "#fff",
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "#2eb85c",
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        data: revenueData,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: "index", intersect: false },
                plugins: {
                    legend: {
                        position: "top",
                        labels: {
                            usePointStyle: true,
                            pointStyle: "circle",
                            padding: 20,
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(15, 23, 42, 0.9)",
                        titlePadding: 10,
                        bodyPadding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) label += ": ";
                                if (context.parsed.y !== null) {
                                    label += formatPageCurrency(
                                        context.parsed.y,
                                        paymentsChartDataEl,
                                    );
                                }
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // Dashboard Chart Initialization
    const dashboardChartDataEl = document.getElementById(
        "dashboard-chart-data",
    );
    if (dashboardChartDataEl && document.getElementById("mainChart")) {
        const months = JSON.parse(
            dashboardChartDataEl.getAttribute("data-months"),
        );
        const revenueData = JSON.parse(
            dashboardChartDataEl.getAttribute("data-revenue"),
        );
        const expenseData = JSON.parse(
            dashboardChartDataEl.getAttribute("data-expenses"),
        );

        Chart.defaults.color =
            getComputedStyle(document.documentElement).getPropertyValue(
                "--cui-body-color",
            ) || "#8a93a2";
        Chart.defaults.scale.grid.color =
            getComputedStyle(document.documentElement).getPropertyValue(
                "--cui-border-color-translucent",
            ) || "rgba(0,0,0,0.1)";

        const mainChartCtx = document
            .getElementById("mainChart")
            .getContext("2d");

        let gradientRevenue = mainChartCtx.createLinearGradient(0, 0, 0, 400);
        gradientRevenue.addColorStop(0, "rgba(99, 102, 241, 0.5)"); // Indigo
        gradientRevenue.addColorStop(1, "rgba(99, 102, 241, 0.0)");

        let gradientExpense = mainChartCtx.createLinearGradient(0, 0, 0, 400);
        gradientExpense.addColorStop(0, "rgba(239, 68, 68, 0.5)"); // Red
        gradientExpense.addColorStop(1, "rgba(239, 68, 68, 0.0)");

        let mainChart = new Chart(mainChartCtx, {
            type: "line",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Revenue (Paid Bills)",
                        backgroundColor: gradientRevenue,
                        borderColor: "#6366f1",
                        borderWidth: 3,
                        pointBackgroundColor: "#6366f1",
                        pointBorderColor: "#fff",
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "#6366f1",
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        data: revenueData,
                    },
                    {
                        label: "Society Expenses",
                        backgroundColor: gradientExpense,
                        borderColor: "#ef4444",
                        borderWidth: 3,
                        pointBackgroundColor: "#ef4444",
                        pointBorderColor: "#fff",
                        pointHoverBackgroundColor: "#fff",
                        pointHoverBorderColor: "#ef4444",
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        data: expenseData,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: "index", intersect: false },
                plugins: {
                    legend: {
                        position: "top",
                        labels: {
                            usePointStyle: true,
                            pointStyle: "circle",
                            padding: 20,
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(15, 23, 42, 0.9)",
                        titlePadding: 10,
                        bodyPadding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) label += ": ";
                                if (context.parsed.y !== null)
                                    label += formatPageCurrency(
                                        context.parsed.y,
                                        dashboardChartDataEl,
                                    );
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 1000,
                        grid: { borderDash: [4, 4] },
                        ticks: {
                            callback: function (value) {
                                return formatPageCurrency(value, dashboardChartDataEl);
                            },
                        },
                    },
                    x: { grid: { display: false } },
                },
            },
        });

        // Status Doughnut Chart (Maintenance Tracker)
        let statusChart = null;
        if (document.getElementById("statusChart")) {
            const statusChartCtx = document
                .getElementById("statusChart")
                .getContext("2d");
            const statusData = JSON.parse(
                dashboardChartDataEl.getAttribute("data-status") || "{}",
            );

            let paidCount = statusData.paid !== undefined ? Number(statusData.paid) : Number(statusData[0] || 0);
            let pendingCount = statusData.pending !== undefined ? Number(statusData.pending) : Number(statusData[1] || 0);

            let labels = ["Collected", "Pending"];
            let data = [paidCount, pendingCount];
            let colors = ["#10b981", "#f59e0b"];

            const totalBillsCount = paidCount + pendingCount;
            if (totalBillsCount === 0) {
                labels = ["No Bills Generated Yet"];
                data = [1];
                colors = ["#334155"];
            }

            statusChart = new Chart(statusChartCtx, {
                type: "doughnut",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                        tooltip: {
                            backgroundColor: "rgba(15, 23, 42, 0.9)",
                            titlePadding: 10,
                            bodyPadding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    if (totalBillsCount === 0) {
                                        return " No bills generated";
                                    }
                                    let label = context.label || "";
                                    if (label) label += ": ";
                                    if (context.parsed !== null)
                                        label += formatPageCurrency(
                                            context.parsed,
                                            dashboardChartDataEl,
                                        );
                                    return label;
                                },
                            },
                        },
                    },
                    cutout: "75%",
                    layout: { padding: 10 },
                },
            });
        }

        // Expense Breakdown Pie Chart
        let expenseChart = null;
        if (document.getElementById("expenseBreakdownChart")) {
            const expenseChartCtx = document
                .getElementById("expenseBreakdownChart")
                .getContext("2d");
            let expenseLabels = JSON.parse(
                dashboardChartDataEl.getAttribute("data-expense-labels") || "[]",
            );
            let expenseData = JSON.parse(
                dashboardChartDataEl.getAttribute("data-expense-data") || "[]",
            );

            let vibrantColors = [
                "#6366f1",
                "#ec4899",
                "#f59e0b",
                "#10b981",
                "#8b5cf6",
                "#ef4444",
                "#0ea5e9",
                "#14b8a6",
            ];

            const totalExpenseSum = expenseData.reduce((acc, val) => acc + Number(val || 0), 0);
            if (!expenseData.length || totalExpenseSum === 0) {
                expenseLabels = ["No Expenses Recorded Yet"];
                expenseData = [1];
                vibrantColors = ["#334155"];
            }

            expenseChart = new Chart(expenseChartCtx, {
                type: "pie",
                data: {
                    labels: expenseLabels,
                    datasets: [
                        {
                            data: expenseData,
                            backgroundColor: vibrantColors,
                            borderWidth: 0,
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                        tooltip: {
                            backgroundColor: "rgba(15, 23, 42, 0.9)",
                            titlePadding: 10,
                            bodyPadding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    if (totalExpenseSum === 0) {
                                        return " No expenses logged";
                                    }
                                    let label = context.label || "";
                                    if (label) label += ": ";
                                    if (context.parsed !== null)
                                        label += formatPageCurrency(
                                            context.parsed,
                                            dashboardChartDataEl,
                                        );
                                    return label;
                                },
                            },
                        },
                    },
                    layout: { padding: 10 },
                },
            });
        }

        // Occupancy Rates Doughnut Chart
        let occupancyChart = null;
        if (document.getElementById("occupancyChart")) {
            const occupancyChartCtx = document
                .getElementById("occupancyChart")
                .getContext("2d");
            const occupancyData = JSON.parse(
                dashboardChartDataEl.getAttribute("data-occupancy") || "{}",
            );

            let occupiedCount = occupancyData.occupied !== undefined ? Number(occupancyData.occupied) : Number(occupancyData[0] || 0);
            let emptyCount = occupancyData.empty !== undefined ? Number(occupancyData.empty) : Number(occupancyData[1] || 0);

            let labels = ["Occupied", "Empty"];
            let data = [occupiedCount, emptyCount];
            let colors = ["#3b82f6", "#e2e8f0"];

            const totalFlatsCount = occupiedCount + emptyCount;
            if (totalFlatsCount === 0) {
                labels = ["No Flats Registered Yet"];
                data = [1];
                colors = ["#334155"];
            }

            occupancyChart = new Chart(occupancyChartCtx, {
                type: "doughnut",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                        tooltip: {
                            backgroundColor: "rgba(15, 23, 42, 0.9)",
                            titlePadding: 10,
                            bodyPadding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    if (totalFlatsCount === 0) {
                                        return " No flats registered";
                                    }
                                    let label = context.label || "";
                                    if (label) label += ": ";
                                    if (context.parsed !== null)
                                        label += context.parsed + " Flats";
                                    return label;
                                },
                            },
                        },
                    },
                    cutout: "65%",
                    layout: { padding: 10 },
                },
            });
        }

        // Update charts on color scheme change
        document.documentElement.addEventListener("ColorSchemeChange", () => {
            Chart.defaults.color =
                getComputedStyle(document.documentElement).getPropertyValue(
                    "--cui-body-color",
                ) || "#8a93a2";
            Chart.defaults.scale.grid.color =
                getComputedStyle(document.documentElement).getPropertyValue(
                    "--cui-border-color-translucent",
                ) || "rgba(0,0,0,0.1)";
            if (mainChart) mainChart.update();
            if (statusChart) statusChart.update();
            if (expenseChart) expenseChart.update();
            if (occupancyChart) occupancyChart.update();
        });
    }
});

//optimized and cleaned up version of the above code
$(document).ready(function () {
    // SweetAlert Configuration
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-danger mx-2",
            cancelButton: "btn btn-secondary",
        },
        buttonsStyling: false,
    });

    // Toastr Configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 3000,
    };

    // CSRF TOKEN
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
    });

    // Initialize Select2 for filters
    if (typeof $.fn.select2 !== "undefined") {
        $(".select2-filter").select2({
            theme: "bootstrap-5",
            width: "100%",
        });
    }

    // Helper function to initialize modals
    function initializeModal(elementId) {
        const modalEl = document.getElementById(elementId);
        const ModalClass = window.coreui?.Modal || window.bootstrap?.Modal;
        return modalEl && ModalClass
            ? ModalClass.getOrCreateInstance(modalEl)
            : null;
    }

    // Modal Instances
    const userModalInstance = initializeModal("user-modal");
    const blockModalInstance = initializeModal("block-modal");
    const flatModalInstance = initializeModal("flat-modal");
    const complainModalInstance = initializeModal("complain-modal");
    const expenseModalInstance = initializeModal("expense-modal");
    const expenseCategoryModalInstance = initializeModal(
        "expense-category-modal",
    );
    const flatTypeModalInstance = initializeModal("flat-type-modal");
    const maintenanceBillModalInstance = initializeModal(
        "maintenance-bill-modal",
    );
    const residentModalInstance = initializeModal("resident-modal");

    // Generic function to toggle reset button visibility
    function toggleResetButton(filterId1, filterId2, resetColId) {
        if ($(filterId1).val() || (filterId2 && $(filterId2).val())) {
            $(resetColId).removeClass("d-none");
        } else {
            $(resetColId).addClass("d-none");
        }
    }

    // Generic function for filter change events
    function setupFilterChange(
        filterId,
        tableId,
        columnIndex,
        resetColId,
        filterId2 = null,
    ) {
        $(document).on("change", filterId, function () {
            const value = $(this).val();
            const dataTable = $(tableId).DataTable();
            if (typeof columnIndex === "string") {
                dataTable.column(columnIndex).search(value).draw();
            } else {
                dataTable.column(columnIndex).search(value).draw();
            }
            toggleResetButton(filterId, filterId2, resetColId);
        });
    }

    // Generic function for filter reset events
    function setupFilterReset(
        resetBtnId,
        filterId1,
        filterId2,
        tableId,
        columnIndex1,
        columnIndex2,
        resetColId,
    ) {
        $(document).on("click", resetBtnId, function () {
            $(filterId1).val("");
            if (filterId2) $(filterId2).val("");

            const dt = $(tableId).DataTable();
            dt.column(columnIndex1).search("");
            if (columnIndex2) dt.column(columnIndex2).search("");
            dt.draw();
            toggleResetButton(filterId1, filterId2, resetColId);
        });
    }

    // User Filters
    setupFilterChange(
        "#users-filter-role",
        "#users-table",
        4,
        "#users-filter-reset-col",
        "#users-filter-status",
    );
    setupFilterChange(
        "#users-filter-status",
        "#users-table",
        5,
        "#users-filter-reset-col",
        "#users-filter-role",
    );
    setupFilterReset(
        "#users-filter-reset",
        "#users-filter-role",
        "#users-filter-status",
        "#users-table",
        4,
        5,
        "#users-filter-reset-col",
    );

    // Flat Filters
    setupFilterChange(
        "#flats-filter-block",
        "#flats-table",
        "block_id:name",
        "#flats-filter-reset-col",
        "#flats-filter-type",
        "#flats-filter-status",
    );
    setupFilterChange(
        "#flats-filter-type",
        "#flats-table",
        "flat_type_id:name",
        "#flats-filter-reset-col",
        "#flats-filter-block",
        "#flats-filter-status",
    );
    setupFilterChange(
        "#flats-filter-status",
        "#flats-table",
        "status:name",
        "#flats-filter-reset-col",
        "#flats-filter-block",
        "#flats-filter-type",
    );

    $("#flats-filter-reset").on("click", function () {
        $("#flats-filter-block").val("").trigger("change.select2");
        $("#flats-filter-type").val("").trigger("change.select2");
        $("#flats-filter-status").val("").trigger("change.select2");

        const dt = $("#flats-table").DataTable();
        dt.column("block_id:name").search("");
        dt.column("flat_type_id:name").search("");
        dt.column("status:name").search("");
        dt.draw();

        $("#flats-filter-reset-col").addClass("d-none");
    });

    // Resident Filters
    setupFilterChange(
        "#residents-filter-block",
        "#residents-table",
        "block:name",
        "#residents-filter-reset-col",
    );
    setupFilterReset(
        "#residents-filter-reset",
        "#residents-filter-block",
        null,
        "#residents-table",
        "block:name",
        null,
        "#residents-filter-reset-col",
    );

    // Maintenance Bills Filters
    setupFilterChange(
        "#maintenance-bills-filter-method",
        "#maintenance-bills-table",
        "payment_method:name",
        "#maintenance-bills-filter-reset-col",
        "#maintenance-bills-filter-block",
    );
    setupFilterChange(
        "#maintenance-bills-filter-block",
        "#maintenance-bills-table",
        "flat:name",
        "#maintenance-bills-filter-reset-col",
        "#maintenance-bills-filter-resident",
    );
    setupFilterChange(
        "#maintenance-bills-filter-resident",
        "#maintenance-bills-table",
        "resident:name",
        "#maintenance-bills-filter-reset-col",
        "#maintenance-bills-filter-block",
    );
    setupFilterChange(
        "#maintenance-bills-filter-year",
        "#maintenance-bills-table",
        "month_year:name",
        "#maintenance-bills-filter-reset-col",
        "#maintenance-bills-filter-block",
    );

    $(document).on("click", "#maintenance-bills-filter-reset", function () {
        $("#maintenance-bills-filter-method").val("").trigger("change.select2");
        $("#maintenance-bills-filter-block").val("").trigger("change.select2");
        $("#maintenance-bills-filter-resident")
            .val("")
            .trigger("change.select2");
        $("#maintenance-bills-filter-year").val("").trigger("change.select2");

        const dt = $("#maintenance-bills-table").DataTable();
        dt.column("payment_method:name").search("");
        dt.column("flat:name").search("");
        dt.column("resident:name").search("");
        dt.column("month_year:name").search("");
        dt.draw();

        $("#maintenance-bills-filter-reset-col").addClass("d-none");
    });

    // Flat Documents Filters
    setupFilterChange(
        "#flat-documents-filter-block",
        "#flat-documents-table",
        "block:name",
        "#flat-documents-filter-reset-col",
    );

    $("#flat-documents-filter-reset").on("click", function () {
        $("#flat-documents-filter-block").val("").trigger("change.select2");

        const dt = $("#flat-documents-table").DataTable();
        dt.column("block:name").search("");
        dt.draw();

        $("#flat-documents-filter-reset-col").addClass("d-none");
    });

    // Expense Filters
    setupFilterChange(
        "#expenses-filter-category",
        "#expenses-table",
        "expense_categories.title:name",
        "#expenses-filter-reset-col",
        "#expenses-filter-user",
        "#expenses-filter-month",
    );
    setupFilterChange(
        "#expenses-filter-user",
        "#expenses-table",
        "users.name:name",
        "#expenses-filter-reset-col",
        "#expenses-filter-category",
        "#expenses-filter-month",
    );
    setupFilterChange(
        "#expenses-filter-month",
        "#expenses-table",
        "expenses.expense_date:name",
        "#expenses-filter-reset-col",
        "#expenses-filter-category",
        "#expenses-filter-user",
    );

    $("#expenses-filter-reset").on("click", function () {
        $("#expenses-filter-category").val("").trigger("change.select2");
        $("#expenses-filter-user").val("").trigger("change.select2");
        $("#expenses-filter-month").val("");

        const dt = $("#expenses-table").DataTable();
        dt.column("expense_categories.title:name").search("");
        dt.column("users.name:name").search("");
        dt.column("expenses.expense_date:name").search("");
        dt.draw();

        $("#expenses-filter-reset-col").addClass("d-none");
    });

    // Generic AJAX form loader
    function loadFormIntoModal(buttonId, modalContentId, modalInstance) {
        $(document).on("click", buttonId, function () {
            const url = $(this).data("url");
            const title = $(this).data("title");

            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    $(modalContentId).html(response);
                    $(`${modalContentId} .modal-title`).text(title);
                    modalInstance?.show();
                },
                error: function () {
                    toastr.error("Could not load form.");
                },
            });
        });
    }

    const documentModalElement = document.getElementById("addDocumentModal");
    const documentModalInstance = documentModalElement
        ? new coreui.Modal(documentModalElement)
        : null;

    loadFormIntoModal(
        '[data-coreui-target="#addDocumentModal"]',
        "#addDocumentModal .modal-content",
        documentModalInstance,
    );

    const viewDocumentModalElement =
        document.getElementById("viewDocumentModal");
    const viewDocumentModalInstance = viewDocumentModalElement
        ? new coreui.Modal(viewDocumentModalElement)
        : null;

    loadFormIntoModal(
        "#flat-documents-table .view-btn",
        "#viewDocumentModal .modal-content",
        viewDocumentModalInstance,
    );

    setupAjaxFormSubmission(
        "#addDocumentForm",
        documentModalInstance,
        "#flat-documents-table",
    );

    setupDeleteHandler(
        "#flat-documents-table .delete-btn",
        "#flat-documents-table",
        "This submission will be deleted permanently!",
    );

    // Dependent dropdown for flat selection in document upload
    $(document).on("change", "#addDocumentForm #block_id", function () {
        var blockId = $(this).val();
        var flatSelect = $("#addDocumentForm #flat_id");
        flatSelect.empty().append('<option value="">Select Flat</option>');

        if (blockId) {
            $.get("/api/flats-by-block/" + blockId, function (data) {
                $.each(data, function (index, flat) {
                    flatSelect.append(
                        '<option value="' +
                            flat.id +
                            '">' +
                            flat.flat_no +
                            "</option>",
                    );
                });
            });
        }
    });

    // Generic AJAX form submission handler
    function setupAjaxFormSubmission(
        formId,
        modalInstance,
        dataTableId,
        successMessage = "Saved successfully.",
        reloadWindowOnSuccess = false,
    ) {
        $(document).on("submit", formId, function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            let requestType = $(this).attr("method") || "POST";
            const formAction = $(this).attr("action");

            const spoofedMethod = $(this).find('input[name="_method"]').val();
            if (spoofedMethod) {
                requestType = spoofedMethod;
            }

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop("disabled", true);

            $(".field-error").remove();
            $(".is-invalid").removeClass("is-invalid");

            $.ajax({
                url: formAction,
                method: "POST", // Always POST for form data with files, spoofing via _method
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $btn.prop("disabled", false);
                    toastr.success(response.message || successMessage);
                    modalInstance?.hide();
                    if (
                        dataTableId &&
                        $.fn.DataTable.isDataTable(dataTableId)
                    ) {
                        $(dataTableId).DataTable().ajax.reload();
                    } else if (
                        dataTableId &&
                        window.LaravelDataTables &&
                        window.LaravelDataTables[dataTableId.substring(1)]
                    ) {
                        window.LaravelDataTables[
                            dataTableId.substring(1)
                        ].ajax.reload();
                    }
                    if (reloadWindowOnSuccess) {
                        setTimeout(function () {
                            window.location.reload();
                        }, 800);
                    }
                },
                error: function (xhr) {
                    $btn.prop("disabled", false);
                    $(".field-error").remove();
                    $(".is-invalid").removeClass("is-invalid");

                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function (key, value) {
                            const field = $(`[name=\"${key}\"]`);
                            field.addClass("is-invalid");
                            const target = field
                                .parent()
                                .hasClass("input-group")
                                ? field.parent()
                                : field;
                            $(
                                '<div class="invalid-feedback d-block field-error text-danger"></div>',
                            )
                                .text(value[0])
                                .insertAfter(target);
                        });
                    } else {
                        toastr.error(
                            xhr.responseJSON?.message ||
                                "Something went wrong.",
                        );
                    }
                },
            });
        });
    }

    // Generic delete handler
    function setupDeleteHandler(
        buttonSelector,
        tableId,
        confirmText,
        successMessage = "Deleted successfully.",
        reloadWindowOnSuccess = false,
    ) {
        $(document).on("click", buttonSelector, function () {
            const url = $(this).data("url");

            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: confirmText,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",
                            success: function (response) {
                                toastr.success(
                                    response.message || successMessage,
                                );
                                if (
                                    tableId &&
                                    $.fn.DataTable.isDataTable(tableId)
                                ) {
                                    $(tableId).DataTable().ajax.reload();
                                } else if (
                                    tableId &&
                                    window.LaravelDataTables &&
                                    window.LaravelDataTables[
                                        tableId.substring(1)
                                    ]
                                ) {
                                    window.LaravelDataTables[
                                        tableId.substring(1)
                                    ].ajax.reload();
                                }
                                if (reloadWindowOnSuccess) {
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 800);
                                }
                            },
                            error: function (xhr) {
                                toastr.error(
                                    xhr.responseJSON?.message ||
                                        "Could not delete.",
                                );
                            },
                        });
                    }
                });
        });
    }

    // User Forms
    loadFormIntoModal(
        "#btn-add-user",
        "#user-modal-content",
        userModalInstance,
    );
    loadFormIntoModal(
        "#users-table .btn-edit-user",
        "#user-modal-content",
        userModalInstance,
    );
    setupAjaxFormSubmission(
        "#user-ajax-form",
        userModalInstance,
        "#users-table",
    );
    setupDeleteHandler(
        "#users-table .btn-delete-user",
        "#users-table",
        "This user will be deleted permanently!",
    );

    // Block Forms
    loadFormIntoModal(
        "#btn-add-block",
        "#block-modal-content",
        blockModalInstance,
    );
    loadFormIntoModal(
        "#blocks-table .btn-edit-block",
        "#block-modal-content",
        blockModalInstance,
    );
    setupAjaxFormSubmission(
        "#block-ajax-form",
        blockModalInstance,
        "#blocks-table",
        "Saved successfully.",
        true,
    );
    setupDeleteHandler(
        "#blocks-table .btn-delete-block",
        "#blocks-table",
        "This block will be deleted permanently!",
        "Deleted successfully.",
        true,
    );

    // Flat Forms
    loadFormIntoModal(
        "#btn-add-flat",
        "#flat-modal-content",
        flatModalInstance,
    );
    loadFormIntoModal(
        "#flats-table .btn-edit-flat",
        "#flat-modal-content",
        flatModalInstance,
    );
    setupAjaxFormSubmission(
        "#flat-ajax-form",
        flatModalInstance,
        "#flats-table",
    );
    setupDeleteHandler(
        "#flats-table .btn-delete-flat",
        "#flats-table",
        "This flat will be deleted permanently!",
    );

    // Complain Forms
    // Fix complaints category filter wiring (UI select -> DataTable column search)
    const complainsFilterCategory = document.getElementById(
        "complains-filter-category",
    );
    if (complainsFilterCategory) {
        $(document).on("change", "#complains-filter-category", function () {
            const value = $(this).val();
            const dtEl = $("#complains-table");
            if (!dtEl.length || !$.fn.DataTable.isDataTable(dtEl)) return;
            const dt = dtEl.DataTable();

            // Column names are defined in ComplainsDataTable: category -> 'category' column
            // Try by name first, then fallback to hardcoded index (4).
            try {
                const col = dt.column("category:name");
                if (col && col.length) {
                    col.search(value || "").draw();
                } else {
                    dt.column(3)
                        .search(value || "")
                        .draw();
                }
            } catch (e) {
                // Fallback: category is the 4th data column in the table definition
                dt.column(3)
                    .search(value || "")
                    .draw();
            }
        });
    }

    loadFormIntoModal(
        "#btn-add-complain",
        "#complain-modal-content",
        complainModalInstance,
    );
    loadFormIntoModal(
        "#complains-table .btn-edit-complain",
        "#complain-modal-content",
        complainModalInstance,
    );
    setupAjaxFormSubmission(
        "#complain-ajax-form",
        complainModalInstance,
        "#complains-table",
    );
    setupDeleteHandler(
        "#complains-table .btn-delete-complain",
        "#complains-table",
        "This complain will be deleted permanently!",
    );

    // Expense Forms
    loadFormIntoModal(
        "#btn-add-expense",
        "#expense-modal-content",
        expenseModalInstance,
    );
    loadFormIntoModal(
        "#expenses-table .btn-edit-expense",
        "#expense-modal-content",
        expenseModalInstance,
    );
    setupAjaxFormSubmission(
        "#expense-ajax-form",
        expenseModalInstance,
        "#expenses-table",
        "Saved successfully.",
        true,
    );
    setupDeleteHandler(
        "#expenses-table .btn-delete-expense",
        "#expenses-table",
        "This expense will be deleted permanently!",
        "Deleted successfully.",
        true,
    );

    // Expense Category Forms
    loadFormIntoModal(
        "#btn-add-expense-category",
        "#expense-category-modal-content",
        expenseCategoryModalInstance,
    );
    loadFormIntoModal(
        "#expense-categories-table .btn-edit-expense-category",
        "#expense-category-modal-content",
        expenseCategoryModalInstance,
    );
    setupAjaxFormSubmission(
        "#expense-category-ajax-form",
        expenseCategoryModalInstance,
        "#expense-categories-table",
    );
    setupDeleteHandler(
        "#expense-categories-table .btn-delete-expense-category",
        "#expense-categories-table",
        "This category will be deleted permanently!",
    );

    // Flat Type Forms
    loadFormIntoModal(
        "#btn-add-flat-type",
        "#flat-type-modal-content",
        flatTypeModalInstance,
    );
    loadFormIntoModal(
        "#flat-types-table .btn-edit-flat-type",
        "#flat-type-modal-content",
        flatTypeModalInstance,
    );
    setupAjaxFormSubmission(
        "#flat-type-ajax-form",
        flatTypeModalInstance,
        "#flat-types-table",
    );
    setupDeleteHandler(
        "#flat-types-table .btn-delete-flat-type",
        "#flat-types-table",
        "This flat type will be deleted permanently!",
    );

    // Maintenance Bill Forms
    loadFormIntoModal(
        "#btn-add-maintenance-bill, #btn-record-payment",
        "#maintenance-bill-modal-content",
        maintenanceBillModalInstance,
    );
    loadFormIntoModal(
        "#maintenance-bills-table .btn-edit-maintenance-bill",
        "#maintenance-bill-modal-content",
        maintenanceBillModalInstance,
    );
    setupAjaxFormSubmission(
        "#maintenance-bill-ajax-form",
        maintenanceBillModalInstance,
        "#maintenance-bills-table",
    );
    setupDeleteHandler(
        "#maintenance-bills-table .btn-delete-maintenance-bill",
        "#maintenance-bills-table",
        "This maintenance bill will be deleted permanently!",
    );

    // Record Payment Form
    loadFormIntoModal(
        "#btn-record-payment",
        "#maintenance-bill-modal-content",
        maintenanceBillModalInstance,
    );
    setupAjaxFormSubmission(
        "#prepayment-form",
        maintenanceBillModalInstance,
        "#maintenance-bills-table",
        "Saved successfully.",
        true,
    );

    // Resident Forms
    loadFormIntoModal(
        "#btn-add-resident",
        "#resident-modal-content",
        residentModalInstance,
    );
    loadFormIntoModal(
        ".btn-edit-resident",
        "#resident-modal-content",
        residentModalInstance,
    );
    setupAjaxFormSubmission(
        "#resident-ajax-form",
        residentModalInstance,
        "#residents-table",
    );
    setupDeleteHandler(
        ".btn-delete-resident",
        "#residents-table",
        "This resident will be deleted permanently!",
    );

    $(document).on("click", ".btn-delete-role", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const url = $(this).data("url");
        const roleName = $(this).data("role-name");

        swalWithBootstrapButtons
            .fire({
                title: "Delete role?",
                text: `Are you sure you want to delete "${roleName}"? This action cannot be undone.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete!",
                cancelButtonText: "Cancel",
                reverseButtons: true,
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "DELETE",
                        success: function (response) {
                            toastr.success(
                                response.message ||
                                    "Role deleted successfully.",
                            );
                            setTimeout(function () {
                                window.location.href =
                                    window.location.pathname + "#role-settings";
                                window.location.reload();
                            }, 800);
                        },
                        error: function (xhr) {
                            toastr.error(
                                xhr.responseJSON?.message ||
                                    "Could not delete role.",
                            );
                        },
                    });
                }
            });
    });

    // Modal Close Cleanup
    $(document).on("click", '[data-coreui-dismiss="modal"]', function () {
        userModalInstance?.hide();
        blockModalInstance?.hide();
        flatModalInstance?.hide();
        complainModalInstance?.hide();
        expenseModalInstance?.hide();
        expenseCategoryModalInstance?.hide();
        flatTypeModalInstance?.hide();
        maintenanceBillModalInstance?.hide();
        residentModalInstance?.hide();
    });

    // Flat Form Block Selection Change
    $(document).on(
        "change",
        '#flat-ajax-form select[name="block_id"]',
        function () {
            const selectedOption = $(this).find("option:selected");
            const maxFloor = selectedOption.data("total-floor");
            const floorInput = $("#floor_no");
            const floorHelp = $("#floor-help");

            if (maxFloor) {
                floorInput.attr("max", maxFloor);
                floorHelp.find("span").text(maxFloor);
                floorHelp.removeClass("d-none");

                if (parseInt(floorInput.val()) > parseInt(maxFloor)) {
                    floorInput.val(maxFloor);
                }
            } else {
                floorInput.removeAttr("max");
                floorHelp.addClass("d-none");
            }
        },
    );

    // Filter flats based on selected block in Resident form
    $(document).on(
        "change",
        "#resident-ajax-form select[name='block_id']",
        function () {
            let blockId = $(this).val();
            let flatSelect = $("#resident-ajax-form select[name='flat_id']");

            if (!blockId) {
                flatSelect.html('<option value="">Select Flat</option>');
                return;
            }

            flatSelect.html('<option value="">Loading...</option>');

            $.ajax({
                url: `/api/flats-by-block/${blockId}`,
                type: "GET",
                success: function (data) {
                    let html = '<option value="">Select Flat</option>';
                    data.forEach(function (flat) {
                        html += `<option value="${flat.id}">${flat.flat_no}</option>`;
                    });
                    flatSelect.html(html);
                },
                error: function () {
                    flatSelect.html(
                        '<option value="">Error loading flats</option>',
                    );
                },
            });
        },
    );

    // Handle flat change to fetch existing owner for rental
    $(document).on(
        "change",
        "#resident-ajax-form select[name='flat_id']",
        function () {
            let flatId = $(this).val();
            let ownerSelect = $(
                "#resident-ajax-form select[name='owner_user_id']",
            );

            if (!flatId) {
                ownerSelect.val("");
                return;
            }

            $.ajax({
                url: `/api/flat-owner/${flatId}`,
                type: "GET",
                success: function (data) {
                    if (data.has_owner && data.user_id) {
                        ownerSelect.val(data.user_id);
                    } else {
                        ownerSelect.val("");
                    }
                },
            });
        },
    );

    // Trigger block change on add/edit flat form open
    $(document).on("ajaxSuccess", function (event, xhr, settings) {
        if (
            settings.url.includes("flats/create") ||
            (settings.url.includes("flats/") && settings.type === "GET")
        ) {
            $('#flat-ajax-form select[name="block_id"]').trigger("change");
        }

        // Initialize Plugins when loaded
        if (
            settings.url.includes("payments/create") ||
            settings.url.includes("maintenance-bills/create") ||
            settings.url.includes("expenses/create") ||
            (settings.url.includes("expenses/") && settings.type === "GET")
        ) {
            try {
                if ($(".dropify").length > 0) {
                    $(".dropify").dropify();
                }
            } catch (e) {
                console.log("Dropify not loaded.");
            }

            // Initialize Select2 for Dropdowns inside the modal
            if (typeof $.fn.select2 !== "undefined") {
                if ($("#resident_id").length > 0) {
                    $("#resident_id").select2({
                        theme: "bootstrap-5",
                        placeholder: "Select Resident",
                        width: "100%",
                        dropdownParent:
                            $("#resident_id").closest(".modal-content"),
                    });
                }
                if ($("#user_id").length > 0) {
                    $("#user_id").select2({
                        theme: "bootstrap-5",
                        placeholder: "Select User",
                        width: "100%",
                        dropdownParent: $("#user_id").closest(".modal-content"),
                    });
                }
            }
            if (typeof flatpickr !== "undefined") {
                try {
                    if ($("#start_date").length > 0) {
                        flatpickr("#start_date", {
                            plugins: [
                                new monthSelectPlugin({
                                    shorthand: true,
                                    dateFormat: "Y-m",
                                    altFormat: "F Y",
                                }),
                            ],
                            onChange: function (
                                selectedDates,
                                dateStr,
                                instance,
                            ) {
                                $("#start_date").val(dateStr);
                                if (
                                    typeof window.calculatePaymentTotals ===
                                    "function"
                                )
                                    window.calculatePaymentTotals();
                            },
                        });
                    }
                    if ($("#end_date").length > 0) {
                        flatpickr("#end_date", {
                            plugins: [
                                new monthSelectPlugin({
                                    shorthand: true,
                                    dateFormat: "Y-m",
                                    altFormat: "F Y",
                                }),
                            ],
                            onChange: function (
                                selectedDates,
                                dateStr,
                                instance,
                            ) {
                                $("#end_date").val(dateStr);
                                if (
                                    typeof window.calculatePaymentTotals ===
                                    "function"
                                )
                                    window.calculatePaymentTotals();
                            },
                        });
                    }
                    if ($("#expense_date").length > 0) {
                        flatpickr("#expense_date", {
                            plugins: [
                                new monthSelectPlugin({
                                    shorthand: true,
                                    dateFormat: "Y-m",
                                    altFormat: "F Y",
                                }),
                            ],
                            onChange: function (
                                selectedDates,
                                dateStr,
                                instance,
                            ) {
                                $("#expense_date").val(dateStr);
                            },
                        });
                    }
                } catch (e) {
                    console.error("Flatpickr init error: ", e);
                }
            }
            setTimeout(function () {
                if ($("#resident_id").length > 0 && $("#resident_id").val()) {
                    $("#resident_id").trigger("change");
                } else if (
                    typeof window.calculatePaymentTotals === "function"
                ) {
                    window.calculatePaymentTotals();
                }
            }, 100);
        }
    });

    // Password Visibility Toggle
    $(document).on("click", ".toggle-password, .toggle-password-btn", function () {
        const container = $(this).closest(".input-group, .position-relative, div");
        const input = container.find("input")[0];
        if (!input) return;
        if (input.type === "password") {
            input.type = "text";
            $(this)
                .attr("aria-label", "Hide password")
                .attr("data-coreui-original-title", "Hide password");
            $(this).find(".fa-eye").removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.type = "password";
            $(this)
                .attr("aria-label", "Show password")
                .attr("data-coreui-original-title", "Show password");
            $(this).find(".fa-eye-slash").removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

    // Reset Password Form Validation
    $(document).on("submit", "#resetPasswordForm", function (e) {
        const emailInput = document.getElementById("email");
        const passwordInput = document.getElementById("new-password");
        const confirmPasswordInput =
            document.getElementById("confirm-password");

        const emailError = document.getElementById("js-email-error");
        const passwordError = document.getElementById("js-password-error");
        const confirmPasswordError = document.getElementById(
            "js-confirm-password-error",
        );

        let isValid = true;

        [emailInput, passwordInput, confirmPasswordInput].forEach((input) => {
            if (!input) return;
            input.classList.remove("is-invalid");
            const bladeError = input.parentElement.querySelector(
                '.invalid-feedback:not([id^="js-"])',
            );
            if (bladeError) bladeError.style.display = "none";
        });

        [emailError, passwordError, confirmPasswordError].forEach((err) => {
            if (err) {
                err.style.display = "none";
                err.textContent = "";
            }
        });

        if (emailInput && !emailInput.value.trim()) {
            isValid = false;
            emailInput.classList.add("is-invalid");
            if (emailError) {
                emailError.textContent = "The email field is required.";
                emailError.style.display = "block";
            }
        } else if (
            emailInput &&
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)
        ) {
            isValid = false;
            emailInput.classList.add("is-invalid");
            if (emailError) {
                emailError.textContent = "Please enter a valid email address.";
                emailError.style.display = "block";
            }
        }

        if (passwordInput && !passwordInput.value) {
            isValid = false;
            passwordInput.classList.add("is-invalid");
            if (passwordError) {
                passwordError.textContent = "The password field is required.";
                passwordError.style.display = "block";
            }
        } else if (passwordInput && passwordInput.value.length < 5) {
            isValid = false;
            passwordInput.classList.add("is-invalid");
            if (passwordError) {
                passwordError.textContent =
                    "The password must be at least 5 characters.";
                passwordError.style.display = "block";
            }
        }

        if (
            passwordInput &&
            passwordInput.value &&
            passwordInput.value.length >= 5 &&
            confirmPasswordInput
        ) {
            if (!confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add("is-invalid");
                if (confirmPasswordError) {
                    confirmPasswordError.textContent =
                        "Please confirm your password.";
                    confirmPasswordError.style.display = "block";
                }
            } else if (passwordInput.value !== confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add("is-invalid");
                if (confirmPasswordError) {
                    confirmPasswordError.textContent =
                        "The password confirmation does not match.";
                    confirmPasswordError.style.display = "block";
                }
            }
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    window.currentMonthlyFee = 0;

    /**
     * Toggles the visibility of UPI payment details based on the selected payment method.
     * Also toggles the 'required' attribute on the payment slip upload field.
     */
    function toggleUpiDetails() {
        const paymentMethod = $("#payment_method").val();
        const upiDetails = $("#upi-details");
        const paymentSlip = $("#payment_slip");
        const transactionId = $("#transaction_id");

        if (paymentMethod === "upi") {
            upiDetails.removeClass("d-none");
            paymentSlip.attr("required", "required");
            transactionId.attr("required", "required");
        } else {
            upiDetails.addClass("d-none");
            paymentSlip.removeAttr("required");
            transactionId.removeAttr("required");
        }
    }

    // Initial call to set the correct state on page load and bind change event
    $(document).on("change", "#payment_method", toggleUpiDetails);

    /**
     * Core frontend engine to calculate dynamically the total maintenance bill.
     * Evaluates the selected date range, fetches the resident's specific fee,
     * and calculates month-by-month penalties or discounts based on global settings.
     *
     * @function calculatePaymentTotals
     */
    window.calculatePaymentTotals = function () {
        const startDateVal = $("#start_date").val();
        const endDateVal = $("#end_date").val();

        let months = 0;

        if (startDateVal && endDateVal) {
            // startDateVal is "YYYY-MM"
            const startParts = startDateVal.split("-");
            const endParts = endDateVal.split("-");

            const startYear = parseInt(startParts[0]);
            const startMonth = parseInt(startParts[1]);
            const endYear = parseInt(endParts[0]);
            const endMonth = parseInt(endParts[1]);

            const start = new Date(startYear, startMonth - 1, 1);
            const end = new Date(endYear, endMonth - 1, 1);

            if (end >= start) {
                // Calculate precise month difference
                months =
                    (end.getFullYear() - start.getFullYear()) * 12 +
                    (end.getMonth() - start.getMonth()) +
                    1;

                if (months < 1) months = 1;

                const monthNames = [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December",
                ];
                $("#hidden_start_month").val(monthNames[start.getMonth()]);
                $("#hidden_start_year").val(start.getFullYear());
            } else {
                months = 0;
            }
        }
        // Update calculated duration display and hidden input
        $("#calculated_duration").text(
            `${months} ${months === 1 ? "Month" : "Month(s)"}`,
        );
        $("#hidden_months").val(months);

        if (
            months > 0 &&
            window.currentMonthlyFee > 0 &&
            window.discountSettings
        ) {
            const subtotal = window.currentMonthlyFee * months;
            const startDate = startDateVal;

            // Calculate split of months
            let pastMonthsCount = 0;
            let futureMonthsCount = 0;

            if (startDate) {
                const start = new Date(startDate);
                const now = new Date();
                pastMonthsCount =
                    (now.getFullYear() - start.getFullYear()) * 12 +
                    (now.getMonth() - start.getMonth());

                if (pastMonthsCount < 0) pastMonthsCount = 0;
                if (pastMonthsCount > months) pastMonthsCount = months;

                futureMonthsCount = months - pastMonthsCount;
            } else {
                futureMonthsCount = months;
            }

            let arrearsAmount = pastMonthsCount * window.currentMonthlyFee;
            let advanceAmount = futureMonthsCount * window.currentMonthlyFee;

            // Penalty Calculation (on past months)
            let penaltyAmount = 0;
            if (
                window.penaltySettings &&
                window.penaltySettings.apply_penalty == "1" &&
                pastMonthsCount > 0
            ) {
                let penaltyValue = 0;
                if (
                    pastMonthsCount >= 12 &&
                    window.penaltySettings.yearly_enabled
                ) {
                    penaltyValue = window.penaltySettings.yearly_value;
                } else if (
                    pastMonthsCount >= 6 &&
                    window.penaltySettings.half_yearly_enabled
                ) {
                    penaltyValue = window.penaltySettings.half_yearly_value;
                } else if (
                    pastMonthsCount >= 3 &&
                    window.penaltySettings.quarterly_enabled
                ) {
                    penaltyValue = window.penaltySettings.quarterly_value;
                } else if (
                    pastMonthsCount >= 1 &&
                    window.penaltySettings.monthly_enabled
                ) {
                    penaltyValue = window.penaltySettings.monthly_value;
                }

                if (penaltyValue > 0) {
                    if (window.penaltySettings.type === "fixed") {
                        penaltyAmount = parseFloat(penaltyValue);
                    } else {
                        let arrearsAmount =
                            pastMonthsCount * window.currentMonthlyFee;
                        penaltyAmount =
                            arrearsAmount * (parseFloat(penaltyValue) / 100);
                    }
                }
            }

            // Discount Calculation (on future months)
            let discountAmount = 0;
            const applyDiscount = window.discountSettings
                ? window.discountSettings.apply_discount
                : "0";
            if (
                (applyDiscount === "1" ||
                    applyDiscount === "true" ||
                    applyDiscount === "on") &&
                futureMonthsCount > 0
            ) {
                let discountValue = 0;
                if (
                    futureMonthsCount >= 12 &&
                    window.discountSettings.yearly_enabled
                ) {
                    discountValue = window.discountSettings.yearly_value;
                } else if (
                    futureMonthsCount >= 6 &&
                    window.discountSettings.half_yearly_enabled
                ) {
                    discountValue = window.discountSettings.half_yearly_value;
                } else if (
                    futureMonthsCount >= 3 &&
                    window.discountSettings.quarterly_enabled
                ) {
                    discountValue = window.discountSettings.quarterly_value;
                } else if (
                    futureMonthsCount >= 1 &&
                    window.discountSettings.monthly_enabled
                ) {
                    discountValue = window.discountSettings.monthly_value;
                }

                if (discountValue > 0) {
                    if (window.discountSettings.type === "fixed") {
                        discountAmount = parseFloat(discountValue);
                    } else {
                        let advanceAmount =
                            futureMonthsCount * window.currentMonthlyFee;
                        discountAmount =
                            advanceAmount * (parseFloat(discountValue) / 100);
                    }
                }
            }

            $("#penalty_amount").val(penaltyAmount.toFixed(2));
            $("#discount_applied").val(discountAmount.toFixed(2));
            $("#subtotal").val(subtotal.toFixed(2));

            const totalAmount = (
                subtotal +
                penaltyAmount -
                discountAmount
            ).toFixed(2);
            $("#total_amount").val(totalAmount);

            $("#submit-btn").prop("disabled", false);
        } else {
            $("#subtotal").val((0).toFixed(2));
            $("#penalty_amount").val((0).toFixed(2));
            $("#discount_applied").val((0).toFixed(2));
            $("#total_amount").val((0).toFixed(2));

            $("#submit-btn").prop("disabled", true);
        }
    };

    // Bind payment calculation to date changes
    $(document).on(
        "change",
        "#start_date, #end_date",
        window.calculatePaymentTotals,
    );

    // Update fee and recalculate when resident changes
    $(document).on("change", "#resident_id", function () {
        const resId = $(this).val();

        if (
            resId &&
            window.residentFees &&
            typeof window.residentFees[resId] !== "undefined"
        ) {
            window.currentMonthlyFee =
                parseFloat(window.residentFees[resId]) || 0;
            $("#display_monthly_fee").text(window.currentMonthlyFee.toFixed(2));
            $("#display_monthly_total").text(
                window.currentMonthlyFee.toFixed(2),
            );
            $("#maintenance-fees-section").slideDown();
        } else {
            window.currentMonthlyFee = 0;
            $("#maintenance-fees-section").slideUp();
        }
        window.calculatePaymentTotals();
    });

    // Also recalculate when penalty or discount amounts are manually changed
    $(document).on(
        "input blur",
        "#penalty_amount, #discount_applied",
        function () {
            const subtotal = parseFloat($("#subtotal").val()) || 0;
            const penalty = parseFloat($("#penalty_amount").val()) || 0;
            const discount = parseFloat($("#discount_applied").val()) || 0;

            let total = subtotal + penalty - discount;
            if (total < 0) total = 0;

            $("#total_amount").val(total.toFixed(2));
        },
    );

    // Initial calls
    toggleUpiDetails();
    window.calculatePaymentTotals();
});

// --- Global Layout Scripts ---
$(document).ready(function () {
    if ($('input[type="file"]:not(.no-dropify)').length) {
        $('input[type="file"]:not(.no-dropify)').dropify();
    }
    const flashMessages = document.getElementById("global-flash-messages");
    if (flashMessages) {
        if (flashMessages.dataset.success)
            toastr.success(flashMessages.dataset.success);
        if (flashMessages.dataset.error)
            toastr.error(flashMessages.dataset.error);
        if (flashMessages.dataset.status)
            toastr.success(flashMessages.dataset.status);
        if (flashMessages.dataset.validation)
            toastr.error(flashMessages.dataset.validation);
    }
});

// --- Maintenance Report Scripts ---
document.addEventListener("DOMContentLoaded", function () {
    const reportTypeSelect = document.getElementById("reportTypeSelect");
    if (reportTypeSelect) {
        reportTypeSelect.addEventListener("change", function () {
            if (this.value === "yearly") {
                document.getElementById("monthContainer").style.display =
                    "none";
            } else {
                document.getElementById("monthContainer").style.display =
                    "block";
            }
        });
    }

    if (typeof $ !== "undefined") {
        if ($("#paidTable").length) {
            $("#paidTable").DataTable({
                pageLength: 25,
                responsive: true,
            });
        }
        if ($("#pendingTable").length) {
            $("#pendingTable").DataTable({
                pageLength: 25,
                responsive: true,
            });
        }
        if ($("#yearlyTable").length) {
            $("#yearlyTable").DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: false,
            });
        }
    }
});

// --- Dashboard Scripts ---
document.addEventListener("DOMContentLoaded", function () {
    // Counter Animation
    const counters = document.querySelectorAll(".counter-animate");
    const speed = 200;

    if (counters.length > 0) {
        counters.forEach((counter) => {
            const updateCount = () => {
                const target = +counter.getAttribute("data-target");
                const count = +counter.innerText.replace(/,/g, "");
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc).toLocaleString();
                    setTimeout(updateCount, 10);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };
            updateCount();
        });
    }
});

// --- Flat Documents Modal Scripts ---
$(document).on("shown.coreui.modal", "#addDocumentModal", function () {
    $("#addDocumentModal .select2").select2({
        dropdownParent: $("#addDocumentModal"),
    });
});

$(document).on("change", "#addDocumentModal #block_id", function () {
    var blockId = $(this).val();
    $("#addDocumentModal #flat_id").html(
        '<option value="">Select Flat</option>',
    );
    $("#addDocumentModal #user_id").html(
        '<option value="">Select Resident</option>',
    );
    resetResidentInfo();

    if (blockId) {
        $.ajax({
            url: "/api/flats-by-block/" + blockId,
            type: "GET",
            success: function (data) {
                $.each(data, function (key, flat) {
                    $("#addDocumentModal #flat_id").append(
                        '<option value="' +
                            flat.id +
                            '">' +
                            flat.flat_no +
                            "</option>",
                    );
                });
            },
        });
    }
});

$(document).on("change", "#addDocumentModal #flat_id", function () {
    var flatId = $(this).val();
    $("#addDocumentModal #user_id").html(
        '<option value="">Select Resident</option>',
    );
    resetResidentInfo();

    if (flatId) {
        $.ajax({
            url: "/api/flat-users/" + flatId,
            type: "GET",
            success: function (data) {
                $.each(data, function (key, user) {
                    var typeLabel =
                        user.resident_type === "owner" ? "Owner" : "Tenant";
                    $("#addDocumentModal #user_id").append(
                        '<option value="' +
                            user.id +
                            '" data-phone="' +
                            (user.phone || "N/A") +
                            '" data-email="' +
                            (user.email || "N/A") +
                            '" data-type="' +
                            user.resident_type +
                            '">' +
                            user.name +
                            " (" +
                            typeLabel +
                            ")</option>",
                    );
                });
            },
        });
    }
});

$(document).on("change", "#addDocumentModal #user_id", function () {
    var selected = $(this).find("option:selected");
    var userId = selected.val();

    if (userId) {
        var phone = selected.data("phone");
        var email = selected.data("email");
        var type = selected.data("type");

        $("#addDocumentModal #resident_phone").val(phone);
        $("#addDocumentModal #resident_email").val(email);
        $("#addDocumentModal #resident_type").val(type);
        $("#addDocumentModal #resident_info_container").removeClass("d-none");

        generateDocumentInputs(type);
        $("#addDocumentModal #submitBtn").prop("disabled", false);
    } else {
        resetResidentInfo();
    }
});

function resetResidentInfo() {
    $("#addDocumentModal #resident_info_container").addClass("d-none");
    $("#addDocumentModal #resident_phone").val("");
    $("#addDocumentModal #resident_email").val("");
    $("#addDocumentModal #resident_type").val("");
    $("#addDocumentModal #dynamic_documents_container").html(
        '<p class="text-muted small">Please select a resident to view required documents.</p>',
    );
    $("#addDocumentModal #submitBtn").prop("disabled", true);
}

function generateDocumentInputs(residentType) {
    var container = $("#addDocumentModal #dynamic_documents_container");
    var form = document.getElementById("addDocumentForm");
    container.empty();

    if (!form) return;

    var appSettings = JSON.parse(form.dataset.settings || "{}");
    var documentRequirements = JSON.parse(form.dataset.requirements || "{}");
    var docs = documentRequirements[residentType] || {};
    var hasRequiredDocs = false;

    $.each(docs, function (key, label) {
        var settingKey = "req_doc_" + residentType + "_" + key;

        if (appSettings && appSettings[settingKey] == "1") {
            hasRequiredDocs = true;
            var html = `
            <div class="mb-3">
                <label class="form-label fw-semibold text-body">${label} <span class="text-danger">*</span></label>
                <input type="file" class="form-control bg-white text-dark" name="${settingKey}" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
            `;
            container.append(html);
        }
    });

    if (!hasRequiredDocs) {
        container.html(
            '<p class="text-muted small">No documents are required for this resident type based on global settings.</p>',
        );
        $("#addDocumentModal #submitBtn").prop("disabled", true);
    }
}

// --- Flats Index Scripts ---
$(document).on("click", ".btn-history-flat", function () {
    let url = $(this).data("url");
    $("#flat-history-modal-content").html(
        '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><div class="mt-2 text-muted">Loading history...</div></div>',
    );
    $("#flat-history-modal").modal("show");

    $.get(url, function (data) {
        $("#flat-history-modal-content").html(data);
    }).fail(function () {
        $("#flat-history-modal-content").html(
            '<div class="p-4 text-center text-danger">Failed to load history. Please try again.</div>',
        );
    });
});

$(document).on("click", ".btn-transfer-flat", function () {
    let url = $(this).data("url");
    $("#flat-modal-content").html(
        '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><div class="mt-2 text-muted">Loading transfer form...</div></div>',
    );
    $("#flat-modal").modal("show");

    $.get(url, function (data) {
        $("#flat-modal-content").html(data);
        if ($("#flat-modal-content .dropify").length) {
            $("#flat-modal-content .dropify").dropify();
        }
    }).fail(function () {
        $("#flat-modal-content").html(
            '<div class="p-4 text-center text-danger">Failed to load transfer form. Please try again.</div>',
        );
    });
});

// --- Maintenance Bills Scripts ---
$(document).ajaxComplete(function (event, xhr, settings) {
    const form = document.getElementById("prepayment-form");
    if (form) {
        window.residentFees = JSON.parse(form.dataset.fees || "{}");
        window.discountSettings = JSON.parse(form.dataset.discount || "{}");
        window.penaltySettings = JSON.parse(form.dataset.penalty || "{}");
    }
});

// --- Flats Transfer Scripts ---
$(document).on("change", "#transfer_payment_method", function () {
    if ($(this).val() === "upi") {
        $("#upi_details_container").show();
        $("#payment_slip").prop("required", true);
        $("#transaction_id").prop("required", true);
    } else {
        $("#upi_details_container").hide();
        $("#payment_slip").prop("required", false);
        $("#transaction_id").prop("required", false);
    }
});

$(document).on("submit", "#flat-transfer-form", function (e) {
    e.preventDefault();

    let form = $(this);
    let submitBtn = $("#btn-save-transfer");
    let originalText = submitBtn.html();

    submitBtn
        .html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...',
        )
        .prop("disabled", true);

    // Remove old invalid feedback
    form.find(".is-invalid").removeClass("is-invalid");
    form.find(".invalid-feedback").remove();

    let formData = new FormData(this);

    $.ajax({
        url: form.attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success) {
                $("#flat-modal").modal("hide");
                if (typeof window.LaravelDataTables !== "undefined") {
                    window.LaravelDataTables["flats-table"].ajax.reload(
                        null,
                        false,
                    );
                }
                toastr.success(response.message || "Ownership transferred successfully.");
            } else {
                submitBtn.html(originalText).prop("disabled", false);
                toastr.error(
                    response.message ||
                        "An error occurred while transferring ownership.",
                );
            }
        },
        error: function (xhr) {
            submitBtn.html(originalText).prop("disabled", false);

            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    let input = form.find('[name="' + key + '"]');
                    if (input.length) {
                        input.addClass("is-invalid");
                        input.after(
                            '<div class="invalid-feedback">' +
                                errors[key][0] +
                                "</div>",
                        );
                    }
                }
            } else {
                let msg =
                    xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : "An error occurred while transferring ownership.";
                toastr.error(msg);
            }
        },
    });
});

// --- Name Transfer Bills Scripts ---
let currentStatusUrl = "";

$(document).on("click", ".btn-status", function () {
    currentStatusUrl = $(this).data("url");
    let currentStatus = $(this).data("status");
    $("#bill-status").val(currentStatus).trigger("change");
    $("#status-modal").modal("show");
});

$(document).on("change", "#bill-status", function () {
    if ($(this).val() === "paid") {
        $("#payment-method-container").show();
    } else {
        $("#payment-method-container").hide();
    }
});

$(document).on("submit", "#status-form", function (e) {
    e.preventDefault();
    let btn = $("#btn-save-status");
    let originalText = btn.html();
    btn.html(
        '<span class="spinner-border spinner-border-sm"></span> Saving...',
    ).prop("disabled", true);

    $.ajax({
        url: currentStatusUrl,
        type: "POST",
        data: $(this).serialize(),
        success: function (response) {
            if (response.success) {
                $("#status-modal").modal("hide");
                if (typeof window.LaravelDataTables !== "undefined") {
                    window.LaravelDataTables[
                        "nametransferbills-table"
                    ].ajax.reload(null, false);
                }
                toastr.success(response.message || "Status updated successfully.");
            }
        },
        error: function (xhr) {
            toastr.error("Error updating status");
        },
        complete: function () {
            btn.html(originalText).prop("disabled", false);
        },
    });
});

$(document).on("click", ".btn-delete-bill", function () {
    let url = $(this).data("url");
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: url,
                type: "DELETE",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
            }).catch((error) => {
                let msg =
                    error.responseJSON && error.responseJSON.message
                        ? error.responseJSON.message
                        : "Error deleting bill";
                Swal.showValidationMessage(msg);
            });
        },
        allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof window.LaravelDataTables !== "undefined") {
                window.LaravelDataTables["nametransferbills-table"].ajax.reload(
                    null,
                    false,
                );
            }
            Swal.fire("Deleted!", "The bill has been deleted.", "success");
        }
    });
});

$(document).on("click", ".btn-approve", function () {
    let url = $(this).data("url");

    Swal.fire({
        title: "Approve Transfer?",
        text: "This will update the resident records and finalize the transfer.",
        icon: "info",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, approve it!",
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
            }).catch((error) => {
                let msg =
                    error.responseJSON && error.responseJSON.message
                        ? error.responseJSON.message
                        : "Error approving transfer";
                Swal.showValidationMessage(msg);
            });
        },
        allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof window.LaravelDataTables !== "undefined") {
                window.LaravelDataTables["nametransferbills-table"].ajax.reload(
                    null,
                    false,
                );
            }
            Swal.fire(
                "Approved!",
                "The transfer has been approved successfully.",
                "success",
            );
        }
    });
});

// --- Settings Scripts ---
document.addEventListener("DOMContentLoaded", function () {
    if (
        window.location.pathname !== "/settings" &&
        !document.getElementById("role-permissions-form")
    )
        return;

    // Function to toggle input disabled state based on checkbox
    function toggleInputState(checkboxId, inputName) {
        const checkbox = document.getElementById(checkboxId);
        const input = document.querySelector(`input[name="${inputName}"]`);
        if (checkbox && input) {
            input.disabled = !checkbox.checked;

            // Add event listener for changes
            checkbox.addEventListener("change", function () {
                input.disabled = !this.checked;
            });
        }
    }

    // List of prefixes
    const prefixes = ["penalty", "discount"];
    const phases = ["monthly", "quarterly", "half_yearly", "yearly"];

    prefixes.forEach((prefix) => {
        phases.forEach((phase) => {
            toggleInputState(
                `${prefix}_${phase}_enabled`,
                `${prefix}_${phase}_value`,
            );
        });
    });

    // Update suffix based on penalty/discount type
    function updateSuffix(selectId, suffixClass) {
        const select = document.getElementById(selectId);
        const suffixes = document.querySelectorAll(suffixClass);

        function update() {
            const symbol =
                select.value === "percentage" ? "%" : getPageCurrencySymbol();
            suffixes.forEach((el) => (el.textContent = symbol));
        }

        if (select) {
            update();
            select.addEventListener("change", update);
        }
    }

    updateSuffix("penalty_type", ".penalty-suffix");
    updateSuffix("discount_type", ".discount-suffix");

    const settingsData = document.getElementById("settings-data");
    if (settingsData) {
        const createdRoleId = settingsData.dataset.createdRoleId;
        if (createdRoleId) {
            const card = document.querySelector(
                `.role-card[data-role-id="${createdRoleId}"]`,
            );
            if (card) {
                window.selectRole(card);
            }
        }
    }

    // Check All / Uncheck All Buttons
    document.querySelectorAll(".checkall-btn").forEach((btn) => {
        const targetPrefix = btn.getAttribute("data-target");
        const checkboxes = document.querySelectorAll(
            `input[type="checkbox"][name^="${targetPrefix}"]`,
        );

        // Initialize button state
        const allChecked = Array.from(checkboxes).every((cb) => cb.checked);
        btn.textContent = allChecked ? "Uncheck All" : "Check All";

        // Handle button click
        btn.addEventListener("click", function () {
            const isCheckAll = this.textContent === "Check All";

            checkboxes.forEach((cb) => {
                cb.checked = isCheckAll;
            });

            this.textContent = isCheckAll ? "Uncheck All" : "Check All";
        });

        // Update button state when individual checkboxes change
        checkboxes.forEach((cb) => {
            cb.addEventListener("change", function () {
                const anyUnchecked = Array.from(checkboxes).some(
                    (cb) => !cb.checked,
                );
                btn.textContent = anyUnchecked ? "Check All" : "Uncheck All";
            });
        });
    });
});

window.selectRole = function (element, event) {
    // If the click originated from the dropdown menu, don't trigger role selection
    if (event && event.target.closest(".dropdown")) {
        return;
    }

    // Remove active class from all cards
    document.querySelectorAll(".role-card").forEach((card) => {
        card.classList.remove("border-primary");
        card.classList.add("border-0");
    });

    // Add active class to selected card
    element.classList.remove("border-0");
    element.classList.add("border-primary");

    // Show permissions container, hide placeholder
    document.getElementById("permissions-container").style.display = "block";
    document.getElementById("no-role-selected").style.display = "none";

    // Get role data
    const roleId = element.getAttribute("data-role-id");
    const roleName = element.getAttribute("data-role-name");
    const permissions = JSON.parse(
        element.getAttribute("data-role-permissions"),
    );

    // Set form data
    document.getElementById("selected-role-name").value = roleName;

    // Set form action using base URL
    const baseUrl = window.location.origin + "/roles";
    document.getElementById("role-permissions-form").action =
        baseUrl + "/" + roleId;

    // Uncheck all checkboxes
    document.querySelectorAll(".permission-checkbox").forEach((checkbox) => {
        checkbox.checked = false;
    });

    // Check the ones the role has
    permissions.forEach((permission) => {
        const checkbox = document.querySelector(
            `.permission-checkbox[value="${permission}"]`,
        );
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    // Smooth scroll to permissions table
    document.getElementById("permissions-container").scrollIntoView({
        behavior: "smooth",
        block: "start",
    });
};

// --- Residents Dynamic Import Scripts ---
document.addEventListener("DOMContentLoaded", function () {
    const previewForm = document.getElementById("import-preview-form");
    const processForm = document.getElementById("import-process-form");
    const step1 = document.getElementById("import-step-1");
    const step2 = document.getElementById("import-step-2");
    const btnBack = document.getElementById("btn-back-to-step-1");

    if (!previewForm || !processForm) return;

    const availableFields = {
        "": "Skip Column",
        name: "Resident Name (*)",
        email: "Email Address (*)",
        phone: "Phone Number",
        aadhar_id: "Aadhar ID (*)",
        block_name: "Block Name (*)",
        flat_no: "Flat No (*)",
        type: "Type (owner/rental) (*)",
        move_in_date: "Move In Date",
    };

    const requiredFields = [
        "name",
        "email",
        "aadhar_id",
        "block_name",
        "flat_no",
        "type",
    ];

    previewForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const fileInput = document.getElementById("excel_file");
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            toastr.error("Please select an Excel (.xlsx / .xls) file first.");
            return;
        }

        const submitBtn = document.getElementById("preview-submit-btn");
        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

        const formData = new FormData(this);

        fetch(this.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-eye me-2"></i>Preview Data';

                if (data.success) {
                    document.getElementById("process-file-path").value =
                        data.file_path;

                    // Build Table
                    const dropdownRow = document.getElementById(
                        "preview-dropdown-row",
                    );
                    const tbody = document.getElementById("preview-tbody");

                    dropdownRow.innerHTML = "";
                    tbody.innerHTML = "";

                    // Build Dropdowns
                    data.headers.forEach((headerText, index) => {
                        // Dropdown cell
                        const td = document.createElement("th");
                        const select = document.createElement("select");
                        select.className =
                            "form-select form-select-sm mapping-select";
                        select.dataset.index = index;

                        // Auto-mapping logic
                        let autoSelected = "";
                        const headerLower = (headerText || "")
                            .toLowerCase()
                            .trim();
                        if (
                            headerLower.includes("name") &&
                            !headerLower.includes("block")
                        )
                            autoSelected = "name";
                        else if (headerLower.includes("email"))
                            autoSelected = "email";
                        else if (
                            headerLower.includes("phone") ||
                            headerLower.includes("mobile")
                        )
                            autoSelected = "phone";
                        else if (headerLower.includes("aadhar"))
                            autoSelected = "aadhar_id";
                        else if (headerLower.includes("block"))
                            autoSelected = "block_name";
                        else if (headerLower.includes("flat"))
                            autoSelected = "flat_no";
                        else if (headerLower.includes("type"))
                            autoSelected = "type";
                        else if (headerLower.includes("date"))
                            autoSelected = "move_in_date";

                        Object.keys(availableFields).forEach((key) => {
                            const option = document.createElement("option");
                            option.value = key;
                            option.textContent = availableFields[key];
                            if (key === autoSelected) option.selected = true;
                            select.appendChild(option);
                        });

                        td.appendChild(select);

                        // Add "skip" button below dropdown
                        const skipDiv = document.createElement("div");
                        skipDiv.className = "text-end mt-1";
                        skipDiv.innerHTML =
                            '<a href="#" class="text-danger small text-decoration-none">skip</a>';
                        skipDiv
                            .querySelector("a")
                            .addEventListener("click", function (e) {
                                e.preventDefault();
                                select.value = "";
                            });
                        td.appendChild(skipDiv);

                        dropdownRow.appendChild(td);
                    });

                    // Build Data Rows
                    data.preview_rows.forEach((row) => {
                        const tr = document.createElement("tr");
                        row.forEach((cell) => {
                            const td = document.createElement("td");
                            td.textContent = cell !== null ? cell : "";
                            tr.appendChild(td);
                        });
                        tbody.appendChild(tr);
                    });

                    // Switch steps
                    step1.classList.add("d-none");
                    step2.classList.remove("d-none");
                    document
                        .getElementById("preview-error-alert")
                        .classList.add("d-none");
                } else {
                    toastr.error(data.message || "Error processing file.");
                }
            })
            .catch((error) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-eye me-2"></i>Preview Data';
                toastr.error("A network error occurred.");
                console.error(error);
            });
    });

    btnBack.addEventListener("click", function () {
        step2.classList.add("d-none");
        step1.classList.remove("d-none");
    });

    processForm.addEventListener("submit", function (e) {
        e.preventDefault();

        // Gather mappings
        const selects = document.querySelectorAll(".mapping-select");
        const mapping = {};
        const reverseMapping = {}; // Check for duplicates
        let hasDuplicates = false;

        selects.forEach((select) => {
            const field = select.value;
            const index = select.dataset.index;
            if (field) {
                if (reverseMapping[field]) {
                    hasDuplicates = true;
                }
                reverseMapping[field] = index;
                mapping[field] = index;
            }
        });

        const errorAlert = document.getElementById("preview-error-alert");
        errorAlert.classList.add("d-none");

        if (hasDuplicates) {
            errorAlert.textContent =
                "Error: You cannot map multiple columns to the same field.";
            errorAlert.classList.remove("d-none");
            return;
        }

        const missingFields = [];
        requiredFields.forEach((field) => {
            if (mapping[field] === undefined) {
                missingFields.push(availableFields[field]);
            }
        });

        if (missingFields.length > 0) {
            errorAlert.innerHTML = `<strong>Error:</strong> Missing required mappings for: <br>- ${missingFields.join("<br>- ")}`;
            errorAlert.classList.remove("d-none");
            return;
        }

        // Add mappings to form
        Object.keys(mapping).forEach((field) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = `mapping[${field}]`;
            input.value = mapping[field];
            this.appendChild(input);
        });

        const submitBtn = document.getElementById("process-submit-btn");
        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        const formData = new FormData(this);

        fetch(this.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-file-import me-2"></i>Process Import';

                if (data.success) {
                    step2.classList.add("d-none");
                    const step3 = document.getElementById("import-step-3");
                    if (step3) step3.classList.remove("d-none");

                    document.getElementById(
                        "import-success-count",
                    ).textContent = data.success_count || 0;
                    document.getElementById("import-failed-count").textContent =
                        data.failed_count || 0;

                    if (data.failed_count > 0) {
                        document
                            .getElementById("import-failure-table-container")
                            .classList.remove("d-none");
                        const tbody = document.getElementById(
                            "import-failure-tbody",
                        );
                        tbody.innerHTML = "";
                        data.failed_records.forEach((record) => {
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                            <td>${record.name}</td>
                            <td>${record.block}</td>
                            <td>${record.flat}</td>
                            <td class="text-danger">${record.reason}</td>
                        `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        document.getElementById(
                            "import-success-alert",
                        ).textContent =
                            "All records were imported successfully!";
                        document
                            .getElementById("import-success-alert")
                            .classList.remove("d-none");
                    }
                } else {
                    toastr.error(data.message || "Error processing import.");
                }
            })
            .catch((error) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-file-import me-2"></i>Process Import';
                toastr.error("A network error occurred.");
                console.error(error);
            });
    });
});

// Settings Page & Global Import Hub Scripts
document.addEventListener("DOMContentLoaded", function () {
    if (!window.SMP_SETTINGS_CONFIG) return;

    var config = window.SMP_SETTINGS_CONFIG;
    var currencies = config.availableCurrencies || {};
    var currencySelect = document.getElementById("currency_select");
    var penaltyType = document.getElementById("penalty_type");
    var discountType = document.getElementById("discount_type");

    function currentCurrencySymbol() {
        var selected = currencySelect ? currencySelect.value : "INR";
        return currencies[selected]
            ? currencies[selected].symbol
            : config.currencySymbol;
    }

    function updateCurrencyPreview() {
        var symbol = currentCurrencySymbol();

        document
            .querySelectorAll(".currency-symbol-preview")
            .forEach(function (el) {
                el.textContent = symbol;
            });

        document.querySelectorAll(".penalty-suffix").forEach(function (el) {
            el.textContent =
                penaltyType && penaltyType.value === "fixed" ? symbol : "%";
        });

        document.querySelectorAll(".discount-suffix").forEach(function (el) {
            el.textContent =
                discountType && discountType.value === "fixed" ? symbol : "%";
        });

        if (penaltyType) {
            var penaltyFixedOption = penaltyType.querySelector(
                'option[value="fixed"]',
            );
            if (penaltyFixedOption)
                penaltyFixedOption.textContent =
                    "Fixed Amount (" + symbol + ")";
        }

        if (discountType) {
            var discountFixedOption = discountType.querySelector(
                'option[value="fixed"]',
            );
            if (discountFixedOption)
                discountFixedOption.textContent =
                    "Fixed Amount (" + symbol + ")";
        }
    }

    [currencySelect, penaltyType, discountType].forEach(function (el) {
        if (el) el.addEventListener("change", updateCurrencyPreview);
    });
    if (currencySelect || penaltyType || discountType) updateCurrencyPreview();

    var mapElem = document.getElementById("society_location_map");
    if (mapElem && typeof L !== "undefined") {
        var latInput = document.getElementById("society_latitude");
        var lngInput = document.getElementById("society_longitude");
        var initialLat = (latInput && !isNaN(parseFloat(latInput.value))) ? parseFloat(latInput.value) : (config && !isNaN(parseFloat(config.societyLat)) ? parseFloat(config.societyLat) : 19.0760);
        var initialLng = (lngInput && !isNaN(parseFloat(lngInput.value))) ? parseFloat(lngInput.value) : (config && !isNaN(parseFloat(config.societyLng)) ? parseFloat(config.societyLng) : 72.8777);

        var map = L.map("society_location_map").setView(
            [initialLat, initialLng],
            15,
        );

        L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution:
                '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);

        var marker = L.marker([initialLat, initialLng], {
            draggable: true,
        }).addTo(map);

        function reverseGeocode(lat, lng) {
            fetch(
                "https://nominatim.openstreetmap.org/reverse?format=json&lat=" +
                    lat +
                    "&lon=" +
                    lng,
            )
                .then((response) => response.json())
                .then((data) => {
                    var searchInput =
                        document.getElementById("map_search_input");
                    if (searchInput && data && data.display_name) {
                        searchInput.value = data.display_name;
                    }
                })
                .catch((err) => console.log(err));
        }

        function updateCoordinates(lat, lng, doReverse = false) {
            var latInput = document.getElementById("society_latitude");
            var lngInput = document.getElementById("society_longitude");
            if (latInput && lngInput) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
            }
            if (doReverse) {
                reverseGeocode(lat, lng);
            }
        }

        marker.on("dragend", function (e) {
            var position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng, true);
        });

        map.on("click", function (e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng.lat, e.latlng.lng, true);
        });

        function renderSearchResults(results) {
            var listElem = document.getElementById("search_results_list");
            if (!listElem) return;
            listElem.innerHTML = "";

            if (!results || results.length === 0) {
                listElem.innerHTML =
                    '<div class="list-group-item text-muted small p-3">No societies or matching locations found in India. Try searching city name + area.</div>';
                listElem.classList.remove("d-none");
                return;
            }

            results.forEach(function (place) {
                var item = document.createElement("a");
                item.href = "javascript:void(0)";
                item.className =
                    "list-group-item list-group-item-action p-3 border-bottom d-flex align-items-center";
                var title = place.display_name.split(",")[0] || "Location";
                item.innerHTML =
                    '<div class="bg-light p-2 rounded-circle me-3 text-primary"><i class="fa-solid fa-location-dot"></i></div>' +
                    '<div class="flex-grow-1 overflow-hidden">' +
                    '<div class="fw-semibold text-primary mb-0 text-truncate">' +
                    title +
                    "</div>" +
                    '<div class="small text-muted text-truncate">' +
                    place.display_name +
                    "</div>" +
                    "</div>";

                item.addEventListener("click", function () {
                    var lat = parseFloat(place.lat);
                    var lon = parseFloat(place.lon);
                    map.setView([lat, lon], 17);
                    marker.setLatLng([lat, lon]);
                    updateCoordinates(lat, lon, false);
                    var searchInput =
                        document.getElementById("map_search_input");
                    if (searchInput) searchInput.value = place.display_name;
                    listElem.classList.add("d-none");
                    if (typeof toastr !== "undefined")
                        toastr.success("Location pin updated!");
                });

                listElem.appendChild(item);
            });

            listElem.classList.remove("d-none");
        }

        function searchAddress(query) {
            if (!query || !query.trim()) return;
            if (typeof toastr !== "undefined")
                toastr.info("Searching locations in India...");

            var p1 = fetch(
                "https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=in&limit=6&q=" +
                    encodeURIComponent(query),
            )
                .then((res) => res.json())
                .catch(() => []);
            var p2 = fetch(
                "https://photon.komoot.io/api/?filter=countrycode:in&limit=6&q=" +
                    encodeURIComponent(query),
            )
                .then((res) => res.json())
                .catch(() => ({
                    features: [],
                }));

            Promise.all([p1, p2]).then(function (resArray) {
                var nData = resArray[0] || [];
                var pData = resArray[1] || {
                    features: [],
                };

                var combined = [];
                var seen = new Set();

                nData.forEach(function (item) {
                    var key =
                        parseFloat(item.lat).toFixed(3) +
                        "_" +
                        parseFloat(item.lon).toFixed(3);
                    if (!seen.has(key)) {
                        seen.add(key);
                        combined.push({
                            lat: item.lat,
                            lon: item.lon,
                            display_name: item.display_name,
                        });
                    }
                });

                (pData.features || []).forEach(function (f) {
                    if (f.geometry && f.geometry.coordinates) {
                        var lon = f.geometry.coordinates[0];
                        var lat = f.geometry.coordinates[1];
                        var key =
                            parseFloat(lat).toFixed(3) +
                            "_" +
                            parseFloat(lon).toFixed(3);
                        if (!seen.has(key)) {
                            seen.add(key);
                            var nameParts = [
                                f.properties.name,
                                f.properties.street,
                                f.properties.district,
                                f.properties.city,
                                f.properties.state,
                            ].filter(Boolean);
                            combined.push({
                                lat: lat,
                                lon: lon,
                                display_name: nameParts.join(", "),
                            });
                        }
                    }
                });

                renderSearchResults(combined);
                if (combined.length > 0 && typeof toastr !== "undefined")
                    toastr.success(
                        "Found " + combined.length + " matching locations!",
                    );
            });
        }

        var btnSearch = document.getElementById("btn_search_location");
        var searchInput = document.getElementById("map_search_input");

        if (btnSearch && searchInput) {
            btnSearch.addEventListener("click", function () {
                searchAddress(searchInput.value);
            });
            searchInput.addEventListener("keypress", function (e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    searchAddress(searchInput.value);
                }
            });
            document.addEventListener("click", function (e) {
                var listElem = document.getElementById("search_results_list");
                if (
                    listElem &&
                    !listElem.contains(e.target) &&
                    e.target !== searchInput &&
                    e.target !== btnSearch
                ) {
                    listElem.classList.add("d-none");
                }
            });
        }

        var btnDetect = document.getElementById("btn_get_my_current_location");
        if (btnDetect) {
            btnDetect.addEventListener("click", function () {
                if (navigator.geolocation) {
                    if (typeof toastr !== "undefined")
                        toastr.info("Detecting device GPS location...");
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            var lat = position.coords.latitude;
                            var lng = position.coords.longitude;
                            map.setView([lat, lng], 16);
                            marker.setLatLng([lat, lng]);
                            updateCoordinates(lat, lng, true);
                            if (typeof toastr !== "undefined")
                                toastr.success("Device location detected!");
                        },
                        function (error) {
                            if (typeof toastr !== "undefined")
                                toastr.error(
                                    "Unable to retrieve GPS location. Check browser location permissions.",
                                );
                        },
                    );
                } else {
                    if (typeof toastr !== "undefined")
                        toastr.error(
                            "Geolocation is not supported by your browser.",
                        );
                }
            });
        }
    }

    // Global Import Export Hub Script
    const emSelectAll = document.getElementById("export_master_select_all");
    if (emSelectAll) {
        emSelectAll.addEventListener("change", function () {
            document
                .querySelectorAll(".export-master-chk")
                .forEach((c) => (c.checked = this.checked));
        });
    }

    const globalFileInput = document.getElementById("global_import_file");
    const masterDragDropText = document.getElementById("master-drag-drop-text");
    const masterDragDropZone = document.getElementById("master-drag-drop-zone");
    if (globalFileInput && masterDragDropText) {
        globalFileInput.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                masterDragDropText.textContent = this.files[0].name;
                masterDragDropText.classList.add("text-success");
                if (masterDragDropZone)
                    masterDragDropZone.style.backgroundColor = "#e8f5e9";
            } else {
                masterDragDropText.textContent =
                    "Drag & Drop your Master Excel file here";
                masterDragDropText.classList.remove("text-success");
                if (masterDragDropZone)
                    masterDragDropZone.style.backgroundColor = "#f8f9fa";
            }
        });
    }

    const btnMasterBack = document.getElementById("btn_master_back_to_step_1");
    if (btnMasterBack) {
        btnMasterBack.addEventListener("click", function () {
            document
                .getElementById("master-import-step-2")
                .classList.add("d-none");
            document
                .getElementById("master-import-step-1")
                .classList.remove("d-none");
        });
    }

    const masterModalEl = document.getElementById("master-import-modal");
    if (masterModalEl) {
        masterModalEl.addEventListener("hidden.coreui.modal", function () {
            const s1 = document.getElementById("master-import-step-1");
            const s2 = document.getElementById("master-import-step-2");
            const s3 = document.getElementById("master-import-step-3");
            if (s1) s1.classList.remove("d-none");
            if (s2) s2.classList.add("d-none");
            if (s3) s3.classList.add("d-none");
            if (globalFileInput) globalFileInput.value = "";
            if (masterDragDropText) {
                masterDragDropText.textContent =
                    "Drag & Drop your Master Excel file here";
                masterDragDropText.classList.remove("text-success");
            }
            if (masterDragDropZone)
                masterDragDropZone.style.backgroundColor = "#f8f9fa";
        });
    }

    const btnPreviewImport = document.getElementById(
        "btn_preview_global_import",
    );
    if (btnPreviewImport) {
        btnPreviewImport.addEventListener("click", function () {
            if (
                !globalFileInput ||
                !globalFileInput.files ||
                globalFileInput.files.length === 0
            ) {
                if (typeof toastr !== "undefined")
                    toastr.error(
                        "Please select a Master spreadsheet file (.xlsx, .csv) first.",
                    );
                return;
            }

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Reading Master Sheets...';

            const fd = new FormData();
            fd.append("_token", config.csrfToken);
            fd.append("import_file", globalFileInput.files[0]);

            fetch(config.routes.previewMaster, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                },
                body: fd,
            })
                .then(async (res) => {
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(
                            data.message ||
                                (data.errors
                                    ? Object.values(data.errors)
                                          .flat()
                                          .join(" ")
                                    : "Error reading Master Excel."),
                        );
                    }
                    return data;
                })
                .then((data) => {
                    this.disabled = false;
                    this.innerHTML = originalText;

                    document.getElementById("master_import_file_path").value =
                        data.file_path;
                    const summaryCont = document.getElementById(
                        "master_sheets_summary_container",
                    );
                    summaryCont.innerHTML = "";
                    summaryCont.className = "";
                    summaryCont.style.cssText = "";

                    let maxRows = 0;
                    let allColumns = [];
                    data.sheets_summary.forEach((sheet, sIdx) => {
                        if (sheet.preview_rows.length > maxRows)
                            maxRows = sheet.preview_rows.length;
                        sheet.headers.forEach((h, cIdx) => {
                            allColumns.push({
                                sheetLabel: sheet.label,
                                header: h,
                                sheetIndex: sIdx,
                                colIndex: cIdx,
                            });
                        });
                    });

                    const tableResp = document.createElement("div");
                    tableResp.className =
                        "table-responsive border rounded shadow-sm";
                    tableResp.style.cssText =
                        "max-height: 520px; overflow: auto;";

                    const blurStyle =
                        "backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: color-mix(in srgb, var(--cui-body-bg, #212631) 85%, transparent) !important; position: sticky; top: 0; z-index: 1020;";

                    let theadCols = allColumns
                        .map(
                            (col) => `
                        <th class="text-nowrap text-center align-middle py-3 px-3" style="${blurStyle}">
                            <div class="text-success small mb-1">[${col.sheetLabel}]</div>
                            <div class="fw-bold">${col.header}</div>
                        </th>
                    `,
                        )
                        .join("");

                    let tbodyRows = "";
                    for (let r = 0; r < maxRows; r++) {
                        let rowCols = allColumns
                            .map((col) => {
                                const sheet =
                                    data.sheets_summary[col.sheetIndex];
                                const rowData = sheet.preview_rows[r];
                                const val =
                                    rowData &&
                                    rowData[col.colIndex] !== null &&
                                    rowData[col.colIndex] !== undefined
                                        ? rowData[col.colIndex]
                                        : "";
                                return `<td class="text-nowrap px-3 py-2">${val}</td>`;
                            })
                            .join("");
                        tbodyRows += `<tr><td class="text-center fw-semibold opacity-75">${r + 1}</td>${rowCols}</tr>`;
                    }

                    tableResp.innerHTML = `
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center align-middle" style="min-width: 50px; left: 0; z-index: 1030; ${blurStyle}">#</th>
                                    ${theadCols}
                                </tr>
                            </thead>
                            <tbody>
                                ${tbodyRows}
                            </tbody>
                        </table>
                    `;
                    summaryCont.appendChild(tableResp);

                    document
                        .getElementById("master-import-step-1")
                        .classList.add("d-none");
                    document
                        .getElementById("master-import-step-2")
                        .classList.remove("d-none");
                    document
                        .getElementById("master-import-step-3")
                        .classList.add("d-none");
                })
                .catch((err) => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                    if (typeof toastr !== "undefined")
                        toastr.error(
                            err.message ||
                                "Network error reading Master Excel file.",
                        );
                });
        });
    }

    const btnConfirmImport =
        document.getElementById("btn_confirm_global_import") ||
        document.getElementById("btn_process_global_import");
    if (btnConfirmImport) {
        btnConfirmImport.addEventListener("click", function () {
            const tempFilePath = document.getElementById(
                "global_temp_file_path",
            ).value;
            const targetTable = document.getElementById(
                "global_target_table",
            ).value;
            const selects = document.querySelectorAll(".global-map-select");

            const mapping = {};
            selects.forEach((sel) => {
                if (sel.value !== "") {
                    mapping[sel.getAttribute("data-db-field")] = parseInt(
                        sel.value,
                    );
                }
            });

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Importing Records...';

            fetch(config.routes.processGlobal, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": config.csrfToken,
                },
                body: JSON.stringify({
                    table: targetTable,
                    file_path: tempFilePath,
                    mapping: mapping,
                }),
            })
                .then((res) => res.json())
                .then((data) => {
                    this.disabled = false;
                    this.innerHTML = originalText;

                    if (data.success) {
                        coreui.Modal.getInstance(
                            document.getElementById("globalImportPreviewModal"),
                        ).hide();
                        if (data.failed_count > 0) {
                            if (typeof toastr !== "undefined")
                                toastr.warning(
                                    `Imported ${data.success_count || 0} record(s). ${data.failed_count} failed.`,
                                );
                        } else {
                            if (typeof toastr !== "undefined")
                                toastr.success(
                                    data.message || "Import successful.",
                                );
                        }
                    } else {
                        if (typeof toastr !== "undefined")
                            toastr.error(data.message || "Import error.");
                    }
                })
                .catch((err) => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                    if (typeof toastr !== "undefined")
                        toastr.error("Server error during bulk import.");
                });
        });
    }

    const btnProcessMaster = document.getElementById(
        "btn_process_master_import",
    );
    if (btnProcessMaster) {
        btnProcessMaster.addEventListener("click", function () {
            const filePath = document.getElementById(
                "master_import_file_path",
            ).value;
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Processing & Inserting Records...';

            fetch(config.routes.processMaster, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": config.csrfToken,
                },
                body: JSON.stringify({
                    file_path: filePath,
                }),
            })
                .then((res) => res.json())
                .then((data) => {
                    this.disabled = false;
                    this.innerHTML = originalText;

                    document
                        .getElementById("master-import-step-2")
                        .classList.add("d-none");
                    const step3 = document.getElementById(
                        "master-import-step-3",
                    );
                    step3.classList.remove("d-none");

                    const successAlert = document.getElementById(
                        "master-import-success-alert",
                    );
                    const errorAlert = document.getElementById(
                        "master-import-error-alert",
                    );
                    const successCountSpan = document.getElementById(
                        "master-import-success-count",
                    );
                    const failedCountSpan = document.getElementById(
                        "master-import-failed-count",
                    );
                    const failCont = document.getElementById(
                        "master_import_failure_container",
                    );
                    const failTbody = document.getElementById(
                        "master_import_failure_tbody",
                    );

                    if (successAlert) successAlert.classList.add("d-none");
                    if (errorAlert) errorAlert.classList.add("d-none");
                    if (failCont) failCont.classList.add("d-none");
                    if (failTbody) failTbody.innerHTML = "";

                    const sCount = data.success_count || 0;
                    const fCount = data.failed_count || 0;
                    if (successCountSpan) successCountSpan.textContent = sCount;
                    if (failedCountSpan) failedCountSpan.textContent = fCount;

                    if (data.success && fCount === 0) {
                        if (successAlert) {
                            successAlert.textContent =
                                "All records were imported successfully!";
                            successAlert.classList.remove("d-none");
                        }
                    } else {
                        if (!data.success && errorAlert) {
                            errorAlert.textContent =
                                data.message || "Error occurred during import.";
                            errorAlert.classList.remove("d-none");
                        }
                        if (fCount > 0 && failCont) {
                            failCont.classList.remove("d-none");
                            if (data.failed_records && failTbody) {
                                data.failed_records.forEach((f) => {
                                    const tr = document.createElement("tr");
                                    const rowText =
                                        typeof f.row === "number"
                                            ? "Row " + f.row
                                            : f.row || "";
                                    tr.innerHTML = `
                                        <td>${f.record || "-"}</td>
                                        <td>${f.sheet || ""}</td>
                                        <td>${rowText}</td>
                                        <td class="text-danger">${f.reason || ""}</td>
                                    `;
                                    failTbody.appendChild(tr);
                                });
                            }
                        }
                    }
                })
                .catch((err) => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                    if (typeof toastr !== "undefined")
                        toastr.error(
                            "Server error executing Master Bulk Import.",
                        );
                });
        });
    }

    // ==========================================================================
    // Residents Page Scripts (Filter sync, export checkboxes, drag & drop UI)
    // ==========================================================================
    const blockFilter = document.getElementById("residents-filter-block");
    const modalExportBlock = document.getElementById("modal_export_block");

    if (blockFilter && modalExportBlock) {
        blockFilter.addEventListener("change", function () {
            modalExportBlock.value = blockFilter.value;
        });
    }

    const allCb = document.getElementById("export-all-fields");
    const fieldCbs = document.querySelectorAll(".export-field-cb");
    if (allCb && fieldCbs.length) {
        allCb.addEventListener("change", function () {
            fieldCbs.forEach((cb) => (cb.checked = allCb.checked));
        });
        fieldCbs.forEach((cb) => {
            cb.addEventListener("change", function () {
                allCb.checked = Array.from(fieldCbs).every((c) => c.checked);
            });
        });
    }

    const excelFile = document.getElementById("excel_file");
    const dragDropZone = document.getElementById("drag-drop-zone");
    const dragDropText = document.getElementById("drag-drop-text");
    const dragDropSubtext = document.getElementById("drag-drop-subtext");

    if (excelFile && dragDropZone) {
        excelFile.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                dragDropText.innerHTML = `<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> ${this.files[0].name}</span>`;
                dragDropSubtext.innerHTML = "Click to change file";
                dragDropZone.style.backgroundColor = "rgba(25, 135, 84, 0.05)";
                dragDropZone.style.borderColor = "#198754";
            } else {
                dragDropText.innerHTML = "Drag & Drop your Excel file here";
                dragDropSubtext.innerHTML = "or click to browse";
                dragDropZone.style.backgroundColor = "#f8f9fa";
                dragDropZone.style.borderColor = "#dee2e6";
            }
        });

        dragDropZone.addEventListener("dragover", (e) => {
            dragDropZone.style.backgroundColor = "#e9ecef";
        });
        dragDropZone.addEventListener("dragleave", (e) => {
            if (!excelFile.files || !excelFile.files.length) {
                dragDropZone.style.backgroundColor = "#f8f9fa";
            }
        });
    }

    // ==========================================================================
    // Flat Documents Modal Event Delegation (Edit / Delete)
    // ==========================================================================
    document.addEventListener("click", function (e) {
        const editBtn = e.target.closest(".btn-edit-doc");
        if (editBtn) {
            const key = editBtn.getAttribute("data-key");
            document.getElementById("edit-doc-input-" + key)?.click();
        }

        const deleteBtn = e.target.closest(".btn-delete-doc");
        if (deleteBtn) {
            const url = deleteBtn.getAttribute("data-url");
            const showUrl = document
                .getElementById("flat-doc-modal-config")
                ?.getAttribute("data-show-url");
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "";

            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this document deletion!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(url, {
                            method: "DELETE",
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-TOKEN": csrfToken,
                                Accept: "application/json",
                            },
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success) {
                                    if (typeof toastr !== "undefined")
                                        toastr.success(data.message);
                                    const viewBtn = showUrl
                                        ? document.querySelector(
                                              `[data-url="${showUrl}"]`,
                                          )
                                        : null;
                                    if (viewBtn) {
                                        viewBtn.click();
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    if (typeof toastr !== "undefined")
                                        toastr.error(
                                            data.message ||
                                                "Error deleting document",
                                        );
                                }
                            })
                            .catch((error) => {
                                if (typeof toastr !== "undefined")
                                    toastr.error("A network error occurred.");
                                console.error(error);
                            });
                    }
                });
            }
        }
    });

    document.addEventListener("change", function (e) {
        if (e.target.matches(".edit-doc-input")) {
            const input = e.target;
            if (input.files.length === 0) return;

            const file = input.files[0];
            const url = input.getAttribute("data-url");
            const showUrl = document
                .getElementById("flat-doc-modal-config")
                ?.getAttribute("data-show-url");
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "";

            const formData = new FormData();
            formData.append("file", file);
            formData.append("_token", csrfToken);

            const btn = input.previousElementSibling;
            const originalIcon = btn ? btn.innerHTML : "";
            if (btn) {
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span>';
                btn.disabled = true;
            }

            fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        if (typeof toastr !== "undefined")
                            toastr.success(data.message);
                        const viewBtn = showUrl
                            ? document.querySelector(`[data-url="${showUrl}"]`)
                            : null;
                        if (viewBtn) {
                            viewBtn.click();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        if (typeof toastr !== "undefined")
                            toastr.error(
                                data.message || "Error updating document",
                            );
                    }
                })
                .catch((error) => {
                    if (typeof toastr !== "undefined")
                        toastr.error("A network error occurred.");
                    console.error(error);
                })
                .finally(() => {
                    if (btn) {
                        btn.innerHTML = originalIcon;
                        btn.disabled = false;
                    }
                    input.value = "";
                });
        }
    });
});
