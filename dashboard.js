(function () {
    const chartGrid = "rgba(148, 163, 184, 0.12)";
    const chartTicks = "rgba(203, 213, 225, 0.8)";
    const tooltipOptions = {
        backgroundColor: "rgba(15, 23, 42, 0.96)",
        borderColor: "rgba(51, 65, 85, 1)",
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10,
        titleColor: "#F8FAFC",
        bodyColor: "#CBD5E1"
    };

    const initSidebar = function () {
        const toggle = document.getElementById("toggleSidebar");
        const sidebar = document.getElementById("sidebar");

        if (toggle && sidebar) {
            toggle.addEventListener("click", function () {
                sidebar.classList.toggle("show");
            });
        }
    };

    const initCharts = function () {
        if (typeof Chart === "undefined") {
            return;
        }

        Chart.defaults.color = chartTicks;
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.plugins.legend.labels.boxWidth = 10;
        Chart.defaults.plugins.legend.labels.boxHeight = 10;

        const loginCanvas = document.getElementById("loginChart");
        if (loginCanvas && typeof chartLabels !== "undefined") {
            const ctx = loginCanvas.getContext("2d");
            const successGradient = ctx.createLinearGradient(0, 0, 0, 330);
            successGradient.addColorStop(0, "rgba(34, 197, 94, 0.28)");
            successGradient.addColorStop(1, "rgba(34, 197, 94, 0.02)");

            const failedGradient = ctx.createLinearGradient(0, 0, 0, 330);
            failedGradient.addColorStop(0, "rgba(239, 68, 68, 0.22)");
            failedGradient.addColorStop(1, "rgba(239, 68, 68, 0.02)");

            new Chart(loginCanvas, {
                type: "line",
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: "Login Berhasil",
                            data: typeof chartSuccessData !== "undefined" ? chartSuccessData : [],
                            borderColor: "#22C55E",
                            backgroundColor: successGradient,
                            fill: true,
                            tension: 0.38,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: "#0F172A",
                            pointBorderColor: "#22C55E",
                            pointBorderWidth: 2,
                            borderWidth: 3
                        },
                        {
                            label: "Login Gagal",
                            data: typeof chartFailedData !== "undefined" ? chartFailedData : [],
                            borderColor: "#EF4444",
                            backgroundColor: failedGradient,
                            fill: true,
                            tension: 0.38,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: "#0F172A",
                            pointBorderColor: "#EF4444",
                            pointBorderWidth: 2,
                            borderWidth: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: "index"
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: "bottom",
                            labels: {
                                usePointStyle: true,
                                pointStyle: "circle",
                                padding: 20
                            }
                        },
                        tooltip: tooltipOptions
                    },
                    scales: {
                        x: {
                            grid: { color: chartGrid },
                            ticks: { color: chartTicks }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: chartTicks
                            },
                            grid: { color: chartGrid }
                        }
                    }
                }
            });
        }

        const failCanvas = document.getElementById("failDonut");
        if (failCanvas && typeof fail24Labels !== "undefined" && typeof fail24Data !== "undefined") {
            const ctx = failCanvas.getContext("2d");
            const gradient = ctx.createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, "rgba(37, 99, 235, 0.95)");
            gradient.addColorStop(1, "rgba(37, 99, 235, 0.35)");

            new Chart(failCanvas, {
                type: "bar",
                data: {
                    labels: fail24Labels,
                    datasets: [{
                        label: "Failed Attempts",
                        data: fail24Data,
                        backgroundColor: gradient,
                        borderRadius: 8,
                        maxBarThickness: 14
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipOptions
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: chartTicks,
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 6
                            },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: chartTicks
                            },
                            grid: { color: chartGrid }
                        }
                    }
                }
            });
        }

        const topCanvas = document.getElementById("topAttackersChart");
        if (topCanvas && typeof topAttackersLabels !== "undefined" && typeof topAttackersData !== "undefined") {
            new Chart(topCanvas, {
                type: "bar",
                data: {
                    labels: topAttackersLabels,
                    datasets: [{
                        label: "Failed Attempts",
                        data: topAttackersData,
                        backgroundColor: ["#EF4444", "#F59E0B", "#EAB308", "#22C55E", "#2563EB"],
                        borderRadius: 8,
                        maxBarThickness: 18
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: tooltipOptions
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: chartTicks
                            },
                            grid: { color: chartGrid }
                        },
                        y: {
                            ticks: { color: chartTicks },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    };

    const initNotifications = function () {
        const notifBadge = document.getElementById("notifBadge");
        const notifDropdown = document.getElementById("notifDropdownMenu");

        if (!notifBadge || !notifDropdown || typeof fetch === "undefined") {
            return;
        }

        const renderNotifications = function (data) {
            const unread = data.unread || 0;

            if (unread > 0) {
                notifBadge.style.display = "inline-block";
                notifBadge.textContent = unread > 99 ? "99+" : unread;
            } else {
                notifBadge.style.display = "none";
            }

            let html = "";
            html += "<li><h6 class=\"dropdown-header\">Notifikasi ";
            if (unread > 0) {
                html += "<span class=\"badge bg-danger ms-1\">" + (unread > 99 ? "99+" : unread) + " baru</span>";
            }
            html += "</h6></li>";

            if ((data.detections && data.detections.length === 0) && (data.blocked && data.blocked.length === 0)) {
                html += "<span class=\"dropdown-item\">Belum ada notifikasi.</span>";
            }

            if (data.detections) {
                data.detections.forEach(function (d) {
                    html += "<li><a class=\"dropdown-item py-2\" href=\"detection_logs.php\">";
                    html += "<div class=\"d-flex gap-2\">";
                    html += "<i class=\"bi bi-shield-exclamation text-danger\"></i>";
                    html += "<div><div class=\"fw-semibold\">Brute force terdeteksi</div>";
                    html += "<small class=\"text-muted\">IP " + d.ip_address + " pada " + (new Date(d.detected_at)).toLocaleString() + "</small></div></div></a></li>";
                });
            }

            if (data.blocked) {
                data.blocked.forEach(function (b) {
                    html += "<li><a class=\"dropdown-item py-2\" href=\"blocked_ips.php\">";
                    html += "<div class=\"d-flex gap-2\">";
                    html += "<i class=\"bi bi-ban text-warning\"></i>";
                    html += "<div><div class=\"fw-semibold\">IP diblokir</div>";
                    html += "<small class=\"text-muted\">" + b.ip_address + " sampai " + (new Date(b.blocked_until)).toLocaleString() + "</small></div></div></a></li>";
                });
            }

            if (unread > 0) {
                html += "<li><hr class=\"dropdown-divider\"></li>";
                html += "<li><div class=\"d-flex gap-2 px-3 pb-2\">";
                html += "<a class=\"btn btn-primary btn-sm flex-fill\" href=\"detection_logs.php\">Lihat Log</a>";
                html += "<button id=\"markReadBtn\" class=\"btn btn-outline-secondary btn-sm w-100\">Tandai Dibaca</button>";
                html += "</div></li>";
            }

            notifDropdown.innerHTML = html;
        };

        const fetchNotifications = function () {
            fetch("app/api/notifications.php", { credentials: "same-origin" })
                .then(function (response) { return response.json(); })
                .then(renderNotifications)
                .catch(function () {});
        };

        document.addEventListener("click", function (event) {
            if (event.target && event.target.id === "markReadBtn") {
                event.preventDefault();
                fetch("app/api/notifications_mark_read.php", {
                    method: "POST",
                    credentials: "same-origin"
                })
                    .then(function (response) { return response.json(); })
                    .then(function (result) {
                        if (result.success) {
                            fetchNotifications();
                        }
                    })
                    .catch(function () {});
            }
        });

        fetchNotifications();
        setInterval(fetchNotifications, 20000);
    };

    const initDashboard = function () {
        initSidebar();
        initCharts();
        initNotifications();
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initDashboard);
    } else {
        initDashboard();
    }
})();
