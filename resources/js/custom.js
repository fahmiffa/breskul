import md5 from "blueimp-md5";

export function jadwalForm(initialJadwals = null) {
    const processInitialData = (data) => {
        if (!data || data.length === 0) return [
            { hari: "", mapels: [{ id: null, mapel_id: "", start_time: "", end_time: "" }] }
        ];
        
        // Check if data is already grouped (has 'mapels' property)
        if (data[0] && data[0].hasOwnProperty('mapels')) {
             return data.map(group => ({
                ...group,
                mapels: group.mapels.map(m => ({
                    ...m,
                    mapel_id: m.mapel_id || m.mapel || "", // fallback for mapel_id
                    guru_id: m.guru_id || m.guru || ""     // fallback for guru_id if needed
                }))
             }));
        }

        const groups = {};
        data.forEach(item => {
            if (!groups[item.hari]) {
                groups[item.hari] = {
                    hari: item.hari,
                    mapels: []
                };
            }
            // Ensure mapel_id is present, fallback to mapel if exists
            groups[item.hari].mapels.push({
                ...item,
                mapel_id: item.mapel_id || item.mapel || ""
            });
        });
        
        return Object.values(groups);
    };

    return {
        jadwals: processInitialData(initialJadwals),

        formatWIB(timeStr) {
            if (!timeStr) return "";
            const [hour, minute] = timeStr.split(":");
            const date = new Date();
            date.setHours(parseInt(hour), parseInt(minute), 0, 0);
            return (
                date.toLocaleString("id-ID", {
                    timeZone: "Asia/Jakarta",
                    hour: "2-digit",
                    minute: "2-digit",
                }) + " WIB"
            );
        },

        addJadwal() {
            this.jadwals.push({
                hari: "",
                mapels: [{ id: null, mapel_id: "", start_time: "", end_time: "" }]
            });
        },

        removeJadwal(index) {
            this.jadwals.splice(index, 1);
        },

        addMapel(index) {
            this.jadwals[index].mapels.push({
                id: null,
                mapel_id: "",
                start_time: "",
                end_time: ""
            });
        },

        removeMapel(index, mapelIndex) {
            this.jadwals[index].mapels.splice(mapelIndex, 1);
        },
    };
}

export function trixEditor() {
    return {
        content: "",
        updateTimer: null,
        eventHandlers: [],

        init() {
            const trixEditorElement = this.$refs.trix;
            const inputElement = this.$refs.input;

            if (!trixEditorElement || !inputElement) {
                console.error("Trix editor or input element not found");
                return;
            }

            // Debounced update function
            const updateContent = () => {
                if (this.updateTimer) clearTimeout(this.updateTimer);

                this.updateTimer = setTimeout(() => {
                    if (trixEditorElement && inputElement) {
                        inputElement.value = trixEditorElement.value;
                        this.content = trixEditorElement.value;
                    }
                }, 50);
            };

            // Setup event listeners
            const setupListeners = () => {
                const handlers = [
                    { event: "trix-change", handler: updateContent },
                    {
                        event: "trix-initialize",
                        handler: () => console.log("Trix initialized"),
                    },
                ];

                handlers.forEach(({ event, handler }) => {
                    trixEditorElement.addEventListener(event, handler);
                    this.eventHandlers.push({
                        element: trixEditorElement,
                        event,
                        handler,
                    });
                });
            };

            setupListeners();
        },

        showContent() {
            if (this.$refs.trix) {
                this.content = this.$refs.trix.value;
            }
        },

        clear() {
            if (this.$refs.trix && this.$refs.trix.editor) {
                this.$refs.trix.editor.loadHTML("");
                this.content = "";
            }
        },

        destroy() {
            // Cleanup event listeners
            this.eventHandlers.forEach(({ element, event, handler }) => {
                element.removeEventListener(event, handler);
            });
            this.eventHandlers = [];

            if (this.updateTimer) {
                clearTimeout(this.updateTimer);
            }
        },
    };
}

export const layout = () => {
    return {
        sidebarOpen: true,
        modal: null,
        init() {
            this.sidebarOpen = localStorage.getItem("sidebarOpen") === "true";
            this.modal = this.modalHandler();
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            localStorage.setItem("sidebarOpen", this.sidebarOpen);
        },
        toggleSidebarMobile() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        closeSidebarOnMobile() {
            if (window.innerWidth < 768) {
                this.sidebarOpen = false;
            }
        },
        md5Component(da) {
            return md5(da);
        },
        modalHandler() {
            return {
                activeModal: null,
                openModal(id) {
                    this.activeModal = id;
                    document.body.classList.add("overflow-hidden");
                },
                closeModal() {
                    this.activeModal = null;
                    document.body.classList.remove("overflow-hidden");
                },
            };
        },
    };
};

export const dataTable = (data) => {
    console.log(data);
    return {
        search: "",
        sortColumn: "name",
        sortAsc: true,
        currentPage: 1,
        perPage: 20,
        rows: data,
        selectedRow: null,
        open: false,
        showJob: false,
        showTambahKelas: false,
        selectedItems: [],
        selectedClass: "",
        isLoading: false,
        message: "",
        error: "",

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortColumn = column;
                this.sortAsc = true;
            }
        },

        filteredData() {
            let temp = this.rows.filter((row) =>
                Object.values(row).some((val) => {
                    return String(val)
                        .toLowerCase()
                        .includes(this.search.toLowerCase());
                })
            );

            temp.sort((a, b) => {
                let valA = a[this.sortColumn];
                let valB = b[this.sortColumn];

                if (typeof valA === "string") valA = valA.toLowerCase();
                if (typeof valB === "string") valB = valB.toLowerCase();

                if (valA < valB) return this.sortAsc ? -1 : 1;
                if (valA > valB) return this.sortAsc ? 1 : -1;
                return 0;
            });

            return temp;
        },
        // akademik
        toggleAll() {
            if (this.selectedItems.length === this.rows.length) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.rows.map((i) => i.id);
            }

            console.log(this.selectedItems);
        },
        // payment
        selectAll() {
            if (this.selectedItems.length === this.rows.length) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.rows.map((i) => i.head);
            }

            console.log(this.selectedItems);
        },
        toggleItem(id, event) {
            if (event.target.checked) {
                if (!this.selectedItems.includes(id)) {
                    this.selectedItems.push(id);
                }
            } else {
                this.selectedItems = this.selectedItems.filter((i) => i !== id);
            }

            console.log(this.selectedItems);
        },
        paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredData().slice(start, start + this.perPage);
        },
        totalPages() {
            return Math.ceil(this.filteredData().length / this.perPage);
        },
        nextPage() {
            if (this.currentPage < this.totalPages()) this.currentPage++;
        },
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        deleteRow(e) {
            if (confirm("Yakin ingin menghapus data?")) {
                e.target.submit();
            }
        },
        formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },
        assignClass() {
            this.isLoading = true;
            this.message = "";
            this.error = "";

            console.log(this.selectedClass);

            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            fetch("/dashboard/master/akademik/assign-class", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: JSON.stringify({
                    student_ids: this.selectedItems,
                    class_id: this.selectedClass,
                }),
            })
                .then(async (res) => {
                    this.isLoading = false;
                    if (!res.ok) {
                        let err = await res.json();
                        throw new Error(err.message || "Terjadi kesalahan.");
                    }
                    let data = await res.json();
                    this.message =
                        data.message || "Berhasil menambahkan kelas.";

                    this.selectedItems = [];
                    this.selectedClass = "";
                    setTimeout(() => {
                        this.showTambahKelas = false;
                        location.reload();
                    }, 2000);
                })
                .catch((err) => {
                    this.isLoading = false;
                    this.error = err.message || "Gagal menambahkan kelas.";
                });
        },
        assignPay() {
            this.isLoading = true;
            this.message = "";
            this.error = "";

            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            fetch("/dashboard/pembayaran", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: JSON.stringify({
                    student_ids: this.selectedItems,
                    class_id: this.selectedClass,
                }),
            })
                .then(async (res) => {
                    this.isLoading = false;
                    if (!res.ok) {
                        let err = await res.json();
                        throw new Error(err.message || "Terjadi kesalahan.");
                    }
                    let data = await res.json();
                    this.message =
                        data.message || "Berhasil menambahkan kelas.";

                    this.selectedItems = [];
                    this.selectedClass = "";
                    setTimeout(() => {
                        this.showTambahKelas = false;
                        location.reload();
                    }, 2000);
                })
                .catch((err) => {
                    this.isLoading = false;
                    this.error = err.message || "Gagal menambahkan kelas.";
                });
        },
    };
};

export function currencyInput(initialValue = "") {
    // Pastikan hanya angka yang diambil dari awal
    const cleanValue = (initialValue || "").toString().replace(/\D/g, "");

    console.log(cleanValue);
    return {
        display: formatNumber(cleanValue),
        raw: cleanValue,

        formatInput() {
            const number = this.display.replace(/\D/g, "");
            this.raw = number;
            this.display = formatNumber(number);
        },
    };

    function formatNumber(value) {
        if (!value) return "";
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
}

import Chart from "@toast-ui/chart";
import "@toast-ui/chart/dist/toastui-chart.min.css";

export function salesChart(par, reg) {
    return {
        selectedMonth: new Date().getMonth() + 1,
        selectedYear: new Date().getFullYear(),
        months: [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ],

        async fetchData(actionUrl) {
            const method = "GET";
            fetch(actionUrl, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        "meta[name=csrf-token]"
                    ),
                },
            })
                .then((res) => res.json())
                .then((da) => {
                    this.years = da.Year;
                    this.dummyData = da.data;
                    this.$nextTick(() => {
                        this.renderChart();
                    });
                });
        },

        chartInstance: null,

        updateChart() {
            this.renderChart();
        },

        renderChart() {
            const dataByYear = this.dummyData[this.selectedYear] || {};
            const categories = Object.keys(dataByYear);
            const total = Object.values(dataByYear);

            const chartData = {
                categories: categories,
                series: [
                    {
                        name: par,
                        data: total,
                    },
                ],
            };

            const options = {
                chart: {
                    width: 700,
                    height: 400,
                    title: par,
                    // title: `Grafik Penjualan Tahun ${this.selectedYear}`,
                },
                xAxis: {
                    title: "Bulan",
                },
                yAxis: {
                    title: "Jumlah",
                },
                series: {
                    verticalAlign: true,
                },
                responsive: {
                    animation: true,
                },
            };

            const container = document.getElementById(reg);
            container.innerHTML = "";

            this.chartInstance = Chart.columnChart({
                el: container,
                data: chartData,
                options,
            });
        },
    };
}

export function payChart(par, reg) {
    return {
        selectedMonth: new Date().getMonth() + 1,
        selectedYear: new Date().getFullYear(),
        months: [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ],

        async fetchData(actionUrl) {
            const method = "GET";
            fetch(actionUrl, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        "meta[name=csrf-token]"
                    ),
                },
            })
                .then((res) => res.json())
                .then((da) => {
                    this.years = da.Year;
                    this.dummyData = da.data;
                    this.$nextTick(() => {
                        this.renderChart();
                    });
                });
        },

        chartInstance: null,

        updateChart() {
            this.renderChart();
        },

        renderChart() {
            const dataByYear = this.dummyData[this.selectedYear] || {};
            const categories = Object.keys(dataByYear);
            const total = Object.values(dataByYear);

            const bayarData = categories.map(
                (month) => dataByYear[month]?.bayar || 0
            );
            const belumData = categories.map(
                (month) => dataByYear[month]?.belum || 0
            );

            const chartData = {
                categories: categories,
                series: [
                    {
                        name: "Bayar",
                        data: bayarData,
                    },
                    {
                        name: "Belum Bayar",
                        data: belumData,
                    },
                ],
            };

            const options = {
                chart: {
                    width: 700,
                    height: 400,
                    title: par,
                    // title: `Grafik Penjualan Tahun ${this.selectedYear}`,
                },
                xAxis: {
                    title: "Bulan",
                },
                yAxis: {
                    title: "Jumlah",
                },
                series: {
                    verticalAlign: true,
                },
                responsive: {
                    animation: true,
                },
            };

            const container = document.getElementById(reg);
            container.innerHTML = "";

            this.chartInstance = Chart.columnChart({
                el: container,
                data: chartData,
                options,
            });
        },
    };
}

export function countUp(target) {
    return {
        display: "0",
        current: 0,
        target: target,
        duration: 1000, // in ms
        steps: 60,
        stepValue: 0,

        start() {
            this.stepValue = this.target / this.steps;
            let interval = this.duration / this.steps;
            let counter = setInterval(() => {
                this.current += this.stepValue;
                if (this.current >= this.target) {
                    this.current = this.target;
                    clearInterval(counter);
                }
                this.display = this.formatNumber(Math.floor(this.current));
            }, interval);
        },

        formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },
    };
}

export function generateBill() {
    return {
        progress: 0,
        jobId: null,
        interval: null,
        isLoading: false,
        message: "",
        error: "",

        submitForm() {
            this.isLoading = true;
            this.progress = 0;
            this.jobId = null;
            this.message = "";
            this.error = "";

            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            fetch("/dashboard/bill", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: JSON.stringify({}),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Gagal memulai job");
                    }
                    return response.json();
                })
                .then((data) => {
                    this.jobId = data.jobId;
                    this.pollProgress();
                })
                .catch((error) => {
                    this.error =
                        error.message || "Terjadi kesalahan saat memulai job.";
                    this.isLoading = false;
                });
        },

        pollProgress() {
            this.interval = setInterval(() => {
                if (!this.jobId) return;

                fetch(`/dashboard/job-progress/${this.jobId}`)
                    .then((res) => {
                        if (!res.ok)
                            throw new Error("Gagal mengambil progress");
                        return res.json();
                    })
                    .then((data) => {
                        this.progress = data.progress;

                        if (this.progress >= 100) {
                            clearInterval(this.interval);
                            this.isLoading = false;
                            this.message = "selesai!";
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    })
                    .catch((error) => {
                        clearInterval(this.interval);
                        this.error = "Gagal mendapatkan progress.";
                        this.isLoading = false;
                    });
            }, 1000);
        },
    };
}

export function generateImport() {
    return {
        progress: 0,
        jobId: null,
        interval: null,
        isLoading: false,
        message: "",
        error: "",

        submitForm() {
            this.isLoading = true;
            this.progress = 0;
            this.jobId = null;
            this.message = "";
            this.error = "";

            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            fetch("/dashboard/master/akademik/import", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: JSON.stringify({}),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Gagal memulai job");
                    }
                    return response.json();
                })
                .then((data) => {
                    this.jobId = data.jobId;
                    this.pollProgress();
                })
                .catch((error) => {
                    this.error =
                        error.message || "Terjadi kesalahan saat memulai job.";
                    this.isLoading = false;
                });
        },

        pollProgress() {
            this.interval = setInterval(() => {
                if (!this.jobId) return;

                fetch(`/dashboard/job-progress/${this.jobId}`)
                    .then((res) => {
                        if (!res.ok)
                            throw new Error("Gagal mengambil progress");
                        return res.json();
                    })
                    .then((data) => {
                        this.progress = data.progress;

                        if (this.progress >= 100) {
                            clearInterval(this.interval);
                            this.isLoading = false;
                            this.message = "selesai!";
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    })
                    .catch((error) => {
                        clearInterval(this.interval);
                        this.error = "Gagal mendapatkan progress.";
                        this.isLoading = false;
                    });
            }, 1000);
        },
    };
}

export function generateStudentsImport() {
    return {
        progress: 0,
        jobId: null,
        interval: null,
        isLoading: false,
        message: "",
        error: "",
        file: null,
        kelas: "",

        submitForm() {
            if (!this.file || !this.kelas) {
                this.error = "File dan kelas wajib diisi.";
                return;
            }

            this.isLoading = true;
            this.progress = 0;
            this.jobId = null;
            this.message = "";
            this.error = "";

            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            const formData = new FormData();
            formData.append("file", this.file);
            formData.append("kelas", this.kelas);

            fetch("/dashboard/master/import", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: formData,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Gagal memulai job");
                    }
                    return response.json();
                })
                .then((data) => {
                    this.jobId = data.jobId;
                    this.pollProgress();
                })
                .catch((error) => {
                    this.error =
                        error.message || "Terjadi kesalahan saat memulai job.";
                    this.isLoading = false;
                });
        },

        pollProgress() {
            this.interval = setInterval(() => {
                if (!this.jobId) return;

                fetch(`/dashboard/job-progress/${this.jobId}`)
                    .then((res) => {
                        if (!res.ok)
                            throw new Error("Gagal mengambil progress");
                        return res.json();
                    })
                    .then((data) => {
                        this.progress = data.progress;

                        if (this.progress >= 100) {
                            clearInterval(this.interval);
                            this.isLoading = false;
                            this.message = "selesai!";
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    })
                    .catch(() => {
                        clearInterval(this.interval);
                        this.error = "Gagal mendapatkan progress.";
                        this.isLoading = false;
                    });
            }, 1000);
        },
    };
}
