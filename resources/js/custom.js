import md5 from "blueimp-md5";
import Swal from "sweetalert2";

export function jadwalForm(initialJadwals = null) {
    const processInitialData = (data) => {
        if (!data || data.length === 0)
            return [
                {
                    hari: "",
                    mapels: [
                        {
                            id: null,
                            mapel_id: "",
                            start_time: "",
                            end_time: "",
                        },
                    ],
                },
            ];

        // Check if data is already grouped (has 'mapels' property)
        if (data[0] && data[0].hasOwnProperty("mapels")) {
            return data.map((group) => ({
                ...group,
                mapels: group.mapels.map((m) => ({
                    ...m,
                    mapel_id: m.mapel_id || m.mapel || "", // fallback for mapel_id
                    guru_id: m.guru_id || m.guru || "", // fallback for guru_id if needed
                })),
            }));
        }

        const groups = {};
        data.forEach((item) => {
            if (!groups[item.hari]) {
                groups[item.hari] = {
                    hari: item.hari,
                    mapels: [],
                };
            }
            // Ensure mapel_id is present, fallback to mapel if exists
            groups[item.hari].mapels.push({
                ...item,
                mapel_id: item.mapel_id || item.mapel || "",
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
                mapels: [
                    { id: null, mapel_id: "", start_time: "", end_time: "" },
                ],
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
                end_time: "",
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
        perPage: 10,
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
                }),
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
            if (this.perPage === "all") {
                return this.filteredData();
            }
            const perPage = parseInt(this.perPage) || 10;
            const start = (this.currentPage - 1) * perPage;
            return this.filteredData().slice(start, start + perPage);
        },
        totalPages() {
            if (this.perPage === "all") {
                return 1;
            }
            const perPage = parseInt(this.perPage) || 10;
            return Math.ceil(this.filteredData().length / perPage) || 1;
        },
        nextPage() {
            if (this.currentPage < this.totalPages()) this.currentPage++;
        },
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        deleteRow(e) {
            const form = e.target.tagName === "FORM" ? e.target : e.target.closest("form");
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data yang dihapus beserta relasinya tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed && form) {
                    form.submit();
                }
            });
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
        formatIndo(waktu) {
            if (!waktu) return "-";
            const date = new Date(waktu);
            if (isNaN(date.getTime())) return waktu;
            const days = [
                "Minggu",
                "Senin",
                "Selasa",
                "Rabu",
                "Kamis",
                "Jumat",
                "Sabtu",
            ];
            const months = [
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
            ];

            const dayName = days[date.getDay()];
            const day = date.getDate();
            const monthName = months[date.getMonth()];
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, "0");
            const minutes = String(date.getMinutes()).padStart(2, "0");

            return `${dayName}, ${day} ${monthName} ${year} Jam ${hours}:${minutes}`;
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
                        "meta[name=csrf-token]",
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
                        "meta[name=csrf-token]",
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
                (month) => dataByYear[month]?.bayar || 0,
            );
            const belumData = categories.map(
                (month) => dataByYear[month]?.belum || 0,
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

export function extraForm(students = [], extras = []) {
    return {
        searchStudent: "",
        selectedStudents: [],
        students: students,
        extras: extras,
        showDropdown: false,

        get filteredStudents() {
            let search = this.searchStudent.toLowerCase();
            return this.students.filter((s) => {
                const isSelected = this.selectedStudents.some(
                    (sel) => sel.id === s.id,
                );
                if (isSelected) return false;
                if (search === "") return true;
                return (
                    s.name.toLowerCase().includes(search) ||
                    s.nis.toLowerCase().includes(search)
                );
            });
        },

        selectStudent(student) {
            this.selectedStudents.push(student);
            this.searchStudent = "";
            this.showDropdown = false;
        },

        removeStudent(id) {
            this.selectedStudents = this.selectedStudents.filter(
                (s) => s.id !== id,
            );
        },
    };
}

export function verificationPayment(defaultMonth = "") {
    return {
        rows: [],
        search: "",
        selectedKelas: "",
        selectedMonth: defaultMonth || new Date().toISOString().slice(0, 7),
        sortColumn: "id",
        sortAsc: false,
        currentPage: 1,
        perPage: 10,
        totalPages: 1,
        totalRows: 0,
        from: 0,
        to: 0,
        isLoading: false,
        selectedItems: [],
        selectedClass: "",
        showTambahKelas: false,
        message: "",
        error: "",
        searchTimer: null,

        init() {
            this.fetchData();
        },

        handleSearch() {
            if (this.searchTimer) clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.currentPage = 1;
                this.fetchData();
            }, 350);
        },

        handleFilterChange() {
            this.currentPage = 1;
            this.fetchData();
        },

        async fetchData() {
            this.isLoading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.search || "",
                    kelas: this.selectedKelas || "",
                    month: this.selectedMonth || "",
                    sort_by: this.sortColumn || "id",
                    sort_dir: this.sortAsc ? "asc" : "desc",
                });

                const res = await fetch(`/dashboard/pembayaran?${params.toString()}`, {
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (!res.ok) throw new Error("Gagal mengambil data pembayaran");
                const json = await res.json();

                this.rows = json.data || [];
                this.currentPage = json.current_page || 1;
                this.totalPages = json.last_page || 1;
                this.totalRows = json.total || 0;
                this.from = json.from || 0;
                this.to = json.to || 0;
            } catch (err) {
                console.error("Error fetching payment data:", err);
            } finally {
                this.isLoading = false;
            }
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortColumn = column;
                this.sortAsc = true;
            }
            this.currentPage = 1;
            this.fetchData();
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchData();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.fetchData();
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
                this.currentPage = page;
                this.fetchData();
            }
        },

        selectAll() {
            const pageHeads = this.rows.map((i) => i.head).filter(Boolean);
            const allSelected = pageHeads.length > 0 && pageHeads.every((h) => this.selectedItems.includes(h));

            if (allSelected) {
                this.selectedItems = this.selectedItems.filter((i) => !pageHeads.includes(i));
            } else {
                pageHeads.forEach((h) => {
                    if (!this.selectedItems.includes(h)) {
                        this.selectedItems.push(h);
                    }
                });
            }
        },

        isAllSelected() {
            const pageHeads = this.rows.map((i) => i.head).filter(Boolean);
            return pageHeads.length > 0 && pageHeads.every((h) => this.selectedItems.includes(h));
        },

        toggleItem(id, event) {
            if (event.target.checked) {
                if (!this.selectedItems.includes(id)) {
                    this.selectedItems.push(id);
                }
            } else {
                this.selectedItems = this.selectedItems.filter((i) => i !== id);
            }
        },

        async assignPay() {
            this.isLoading = true;
            this.message = "";
            this.error = "";

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

            try {
                const res = await fetch("/dashboard/pembayaran", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        student_ids: this.selectedItems,
                        class_id: this.selectedClass,
                    }),
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || "Terjadi kesalahan.");

                this.message = data.message || "Pembayaran berhasil ditambahkan.";
                this.selectedItems = [];
                this.selectedClass = "";

                await Swal.fire({
                    title: "Berhasil!",
                    text: this.message,
                    icon: "success",
                    confirmButtonColor: "#22c55e",
                });

                this.showTambahKelas = false;
                this.fetchData();
            } catch (err) {
                this.error = err.message || "Gagal menambahkan pembayaran.";
                Swal.fire({
                    title: "Gagal!",
                    text: this.error,
                    icon: "error",
                    confirmButtonColor: "#ef4444",
                });
            } finally {
                this.isLoading = false;
            }
        },

        async verifyBill(id) {
            const confirmResult = await Swal.fire({
                title: "Verifikasi Pembayaran?",
                text: "Apakah Anda yakin ingin memverifikasi pembayaran ini secara manual?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#22c55e",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Ya, Verifikasi!",
                cancelButtonText: "Batal",
                reverseButtons: true,
            });

            if (!confirmResult.isConfirmed) return;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
                const response = await fetch("/dashboard/pembayaran/verifikasi", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({ id: id }),
                });

                const result = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        title: "Berhasil!",
                        text: result.message || "Pembayaran berhasil diverifikasi.",
                        icon: "success",
                        confirmButtonColor: "#22c55e",
                    });
                    this.fetchData();
                } else {
                    Swal.fire({
                        title: "Gagal!",
                        text: result.message || "Gagal verifikasi pembayaran.",
                        icon: "error",
                        confirmButtonColor: "#ef4444",
                    });
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    title: "Error!",
                    text: "Terjadi kesalahan koneksi.",
                    icon: "error",
                    confirmButtonColor: "#ef4444",
                });
            }
        },
    };
}

export function accountManagement(data, schoolMode = false) {
    return {
        ...dataTable(data),
        passwordModalOpen: false,
        selectedUserId: null,
        selectedUserName: "",
        newPassword: "",
        isLoading: false,
        filterRole: "",
        filterKelas: "",
        schoolMode: schoolMode,

        init() {
            this.$watch("filterRole", (val) => {
                if (val === "Guru") {
                    this.filterKelas = "";
                }
            });
        },

        filteredData() {
            let temp = this.rows.filter((row) => {
                const searchLower = this.search.toLowerCase();
                const rowName = row.data
                    ? (row.data.name || "").toLowerCase()
                    : (row.name || "").toLowerCase();
                const rowUsername = (row.username || "").toLowerCase();
                const matchesSearch =
                    searchLower === "" ||
                    rowName.includes(searchLower) ||
                    rowUsername.includes(searchLower);

                const matchesRole =
                    this.filterRole === "" || row.roles === this.filterRole;

                let matchesKelas = true;
                if (this.filterKelas !== "") {
                    if (row.student_data && row.student_data.reg) {
                        matchesKelas = this.schoolMode
                            ? row.student_data.reg.class_id == this.filterKelas
                            : row.student_data.reg.prodi_id == this.filterKelas;
                    } else {
                        matchesKelas = false;
                    }
                }

                return matchesSearch && matchesRole && matchesKelas;
            });

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
        openPasswordModal(row) {
            this.selectedUserId = row.id;
            this.selectedUserName = row.data ? row.data.name : row.name;
            this.newPassword = "";
            this.passwordModalOpen = true;
        },

        closePasswordModal() {
            this.passwordModalOpen = false;
            this.selectedUserId = null;
            this.newPassword = "";
        },

        async updatePassword() {
            if (this.newPassword.length < 6) {
                alert("Password minimal 6 karakter");
                return;
            }

            this.isLoading = true;
            try {
                const token = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch(
                    "/dashboard/master/akun/password",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": token,
                        },
                        body: JSON.stringify({
                            id: this.selectedUserId,
                            password: this.newPassword,
                        }),
                    },
                );

                const result = await response.json();

                if (response.ok) {
                    alert(result.message);
                    this.closePasswordModal();
                } else {
                    alert(result.message || "Gagal memperbarui password");
                }
            } catch (error) {
                console.error(error);
                alert("Terjadi kesalahan koneksi");
            } finally {
                this.isLoading = false;
            }
        },

        async updateStatus(row) {
            const newStatus = row.status == 1 ? 0 : 1;
            const confirmMessage =
                newStatus == 1 ? "Aktifkan akun ini?" : "Nonaktifkan akun ini?";

            if (!confirm(confirmMessage)) return;

            try {
                const token = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch("/dashboard/master/akun/status", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify({
                        id: row.id,
                        status: newStatus,
                    }),
                });

                const result = await response.json();

                if (response.ok) {
                    // Update local data
                    const index = this.rows.findIndex((r) => r.id === row.id);
                    if (index !== -1) {
                        this.rows[index].status = newStatus;
                        this.rows[index].state =
                            newStatus == 1 ? "Aktif" : "Tidak Aktif";
                    }
                    alert(result.message);
                } else {
                    alert(result.message || "Gagal memperbarui status");
                }
            } catch (error) {
                console.error(error);
                alert("Terjadi kesalahan koneksi");
            }
        },
    };
}

export function ujianAssignmentTable(initialData, classes = []) {
    return {
        ...dataTable(initialData),
        classes: classes,
        selectedClass: "",

        filteredData() {
            let temp = this.rows.filter((row) => {
                const searchLower = this.search.toLowerCase();
                const matchesSearch =
                    row.ujian_nama.toLowerCase().includes(searchLower) ||
                    row.student_name.toLowerCase().includes(searchLower);

                const matchesClass =
                    this.selectedClass === "" ||
                    row.class_ids.includes(parseInt(this.selectedClass));

                return matchesSearch && matchesClass;
            });

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

        confirmDelete(id) {
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Hapus penugasan ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById("deleteForm");
                    if (form) {
                        form.action = `/dashboard/penjadwalan-ujian/${id}`;
                        form.submit();
                    }
                }
            });
        },

        toggleAll() {
            const currentData = this.paginatedData();
            const verifiableIds = currentData
                .filter((row) => row.can_verify)
                .map((row) => row.id);

            const allSelectedInPage = verifiableIds.every((id) =>
                this.selectedItems.includes(id),
            );

            if (allSelectedInPage) {
                this.selectedItems = this.selectedItems.filter(
                    (id) => !verifiableIds.includes(id),
                );
            } else {
                verifiableIds.forEach((id) => {
                    if (!this.selectedItems.includes(id)) {
                        this.selectedItems.push(id);
                    }
                });
            }
        },

        // Detail Modal Logic
        detailModalOpen: false,
        detailData: null,
        detailLoading: false,

        async showDetail(id) {
            this.detailModalOpen = true;
            this.detailLoading = true;
            this.detailData = null;

            try {
                const res = await fetch(`/dashboard/penjadwalan-ujian/${id}`);
                const result = await res.json();
                if (result.success) {
                    this.detailData = result.data;
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.detailLoading = false;
            }
        },

        closeDetailModal() {
            this.detailModalOpen = false;
        },

        stripHtml(html) {
            let tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },

        getScoreColor(isCorrect) {
            return isCorrect ? "text-green-700" : "text-red-700";
        },

        getScoreBg(isCorrect) {
            return isCorrect
                ? "bg-green-100 border-green-200"
                : "bg-red-100 border-red-200";
        },

        isAnswerCorrect(soal, studentKey) {
            if (!this.detailData || !this.detailData.answers) return false;
            const correctValue = this.stripHtml(soal.jawaban).trim();

            let studentValue = studentKey;
            if (["A", "B", "C", "D", "E"].includes(studentKey)) {
                const optKey = "opsi_" + studentKey.toLowerCase();
                studentValue = soal[optKey]
                    ? this.stripHtml(soal[optKey])
                    : studentKey;
            }

            return (
                this.stripHtml(studentValue).trim().toLowerCase() ===
                    correctValue.toLowerCase() ||
                studentKey.toString().toLowerCase() ===
                    correctValue.toLowerCase()
            );
        },

        getStudentValue(soal, studentKey) {
            if (["A", "B", "C", "D", "E"].includes(studentKey)) {
                const optKey = "opsi_" + studentKey.toLowerCase();
                return soal[optKey] ? this.stripHtml(soal[optKey]) : studentKey;
            }
            return studentKey || "-";
        },
    };
}

export function halaqahForm(config = {}) {
    return {
        // Teach (Guru)
        teaches: config.teaches || [],
        teachSearch: config.defaultTeachName || "",
        selectedTeachId: config.defaultTeachId || "",
        showTeachDropdown: false,

        get filteredTeaches() {
            if (!this.teachSearch) return this.teaches;
            const search = this.teachSearch.toLowerCase();
            return this.teaches.filter((t) =>
                t.name && t.name.toLowerCase().includes(search)
            );
        },

        selectTeach(teach) {
            this.selectedTeachId = teach.id;
            this.teachSearch = teach.name;
            this.showTeachDropdown = false;
        },

        // Student selection
        allStudents: config.allStudents || [],
        selectedStudents: config.selectedStudents || [],
        selectMode: "siswa",
        studentSearch: "",
        showStudentDropdown: false,

        // Per Kelas state
        selectedClassId: "",
        classStudents: [],
        selectedClassStudentIds: [],
        isLoadingClassStudents: false,
        classLoaded: false,

        get filteredStudents() {
            const selectedIds = this.selectedStudents.map((s) => s.id);
            let filtered = this.allStudents.filter((s) => !selectedIds.includes(s.id));
            if (this.studentSearch) {
                const search = this.studentSearch.toLowerCase();
                filtered = filtered.filter((s) =>
                    s.name && s.name.toLowerCase().includes(search)
                );
            }
            return filtered;
        },

        addStudent(student) {
            if (!this.selectedStudents.find((s) => s.id === student.id)) {
                this.selectedStudents.push(student);
            }
            this.studentSearch = "";
            this.showStudentDropdown = false;
        },

        removeStudent(index) {
            this.selectedStudents.splice(index, 1);
        },

        async loadStudentsByClass(classId) {
            this.selectedClassId = classId;
            this.classStudents = [];
            this.selectedClassStudentIds = [];
            this.classLoaded = false;

            if (!classId) return;

            this.isLoadingClassStudents = true;
            try {
                const res = await fetch(`/dashboard/master/halaqah/students-by-class?class_id=${classId}`);
                if (!res.ok) throw new Error("Gagal mengambil data siswa kelas");
                const data = await res.json();
                this.classStudents = data;
                this.classLoaded = true;

                // Default check all students from this class who are not yet added
                const alreadySelectedIds = this.selectedStudents.map((s) => s.id);
                this.selectedClassStudentIds = data
                    .filter((s) => !alreadySelectedIds.includes(s.id))
                    .map((s) => s.id);
            } catch (e) {
                console.error("Gagal memuat siswa:", e);
                if (window.Swal) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Gagal memuat daftar siswa per kelas.",
                    });
                }
            } finally {
                this.isLoadingClassStudents = false;
            }
        },

        isClassStudentAlreadyAdded(studentId) {
            return this.selectedStudents.some((s) => s.id === studentId);
        },

        toggleAllClassStudents(event) {
            if (event.target.checked) {
                const alreadySelectedIds = this.selectedStudents.map((s) => s.id);
                this.selectedClassStudentIds = this.classStudents
                    .filter((s) => !alreadySelectedIds.includes(s.id))
                    .map((s) => s.id);
            } else {
                this.selectedClassStudentIds = [];
            }
        },

        isAllClassStudentsChecked() {
            const selectable = this.classStudents.filter(
                (s) => !this.isClassStudentAlreadyAdded(s.id)
            );
            return (
                selectable.length > 0 &&
                selectable.every((s) => this.selectedClassStudentIds.includes(s.id))
            );
        },

        addAllClassStudents() {
            let addedCount = 0;
            this.classStudents.forEach((student) => {
                if (this.selectedClassStudentIds.includes(student.id)) {
                    if (!this.selectedStudents.find((s) => s.id === student.id)) {
                        this.selectedStudents.push(student);
                        addedCount++;
                    }
                }
            });

            // Refresh selectedClassStudentIds
            this.selectedClassStudentIds = [];

            if (window.Swal && addedCount > 0) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: `${addedCount} siswa boarding berhasil ditambahkan ke daftar!`,
                    timer: 1500,
                    showConfirmButton: false,
                });
            }
        },
    };
}

