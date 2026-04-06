@extends('base.layout')
@section('title', 'Dashboard')
@section('content')
<div class="mb-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <form action="{{ route('dashboard.home') }}" method="GET">
        <select name="year" onchange="this.form.submit()" class="border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 p-2 cursor-pointer">
            @for($y = date('Y'); $y >= date('Y')-2; $y--)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
</div>

@if(auth()->user()->role != 3)
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    @if(config('app.school_mode'))
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Kelas</div>
        <div class="text-2xl font-bold text-green-600">{{ $totalKelas }}</div>
    </div>
    @else
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Prodi</div>
        <div class="text-2xl font-bold text-green-600">{{ $totalProdi }}</div>
    </div>
    @endif
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total {{ config('app.school_mode') ? 'Mapel' : 'Makul' }}</div>
        <div class="text-2xl font-bold text-blue-600">{{ $totalMapel }}</div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total {{ config('app.school_mode') ? 'Guru' : 'Dosen' }}</div>
        <div class="text-2xl font-bold text-purple-600">{{ $totalGuru }}</div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total {{ config('app.school_mode') ? 'Murid' : 'Mahasiswa' }}</div>
        <div class="text-2xl font-bold text-orange-600">{{ $totalMurid }}</div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total {{ config('app.school_mode') ? 'Ekskul' : 'UKM' }}</div>
        <div class="text-2xl font-bold text-red-600">{{ $totalEkskul }}</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Payment Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Grafik Pembayaran ({{ $year }})</h3>
        <canvas id="paymentChart"></canvas>
    </div>

    <!-- Attendance Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Grafik Absensi ({{ date('F Y') }})</h3>
        <canvas id="attendanceChart"></canvas>
    </div>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Payment Summary Card -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="text-lg font-semibold opacity-90 mb-1">Total Pembayaran Masuk</h3>
            <div class="text-3xl font-bold mb-4">Rp {{ number_format($totalPaymentPaid, 0, ',', '.') }}</div>
            <div class="flex items-center gap-2 text-sm bg-white/20 w-fit px-3 py-1 rounded-full border border-white/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                Sudah Terverifikasi
            </div>
        </div>
        <svg class="absolute -right-8 -bottom-8 text-white/10 w-40 h-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
    </div>

    <!-- Unpaid Summary Card -->
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="text-lg font-semibold opacity-90 mb-1">Total Belum Dibayar</h3>
            <div class="text-3xl font-bold mb-4">Rp {{ number_format($totalPaymentUnpaid, 0, ',', '.') }}</div>
            <div class="flex items-center gap-2 text-sm bg-white/20 w-fit px-3 py-1 rounded-full border border-white/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                Menunggu Pembayaran
            </div>
        </div>
        <svg class="absolute -right-8 -bottom-8 text-white/10 w-40 h-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="2" y1="10" x2="22" y2="10"></line>
        </svg>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden mb-8">
    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Ringkasan Pengerjaan Ujian</h3>
            <p class="text-sm text-gray-500">Statistik per kelas untuk ujian yang Anda kelola</p>
        </div>
        <a href="{{ route('dashboard.penjadwalan-ujian.index') }}" class="text-sm text-green-600 font-semibold hover:text-green-700 flex items-center gap-1 transition-colors">
            Lihat Detail
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14" />
                <path d="m12 5 7 7-7 7" />
            </svg>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Kelas</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Sudah Mengerjakan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Belum Mengerjakan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($examSummary as $row)
                @php
                $total = $row->sudah + $row->belum;
                $percent = $total > 0 ? round(($row->sudah / $total) * 100) : 0;
                @endphp
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-700">{{ $row->class_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            {{ $row->sudah }} Siswa
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-50 text-orange-700 text-sm font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            {{ $row->belum }} Siswa
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden max-w-[120px]">
                                <div class="bg-green-500 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-600 min-w-[40px]">{{ $percent }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="opacity-20">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            <span>Belum ada data penugasan ujian.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@if(auth()->user()->role != 3)
@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Payment Chart
    const ctxPayment = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctxPayment, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                    label: 'Total Lunas (Rp)',
                    data: @json($paymentData ?? []),
                    backgroundColor: 'rgba(22, 163, 74, 0.7)', // Green-600
                    borderColor: 'rgba(22, 163, 74, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.5,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Total Tagihan (Rp)',
                    data: @json($unpaidPaymentData ?? []),
                    backgroundColor: 'rgba(220, 38, 38, 0.7)', // Red-600
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.5,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('id-ID', {
                                notation: "compact",
                                compactDisplay: "short"
                            }).format(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Attendance Chart
    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxAttendance, {
        type: 'line',
        data: {
            labels: @json($attendanceLabels ?? []),
            datasets: [{
                label: 'Jumlah Kehadiran',
                data: @json($attendanceData ?? []),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: '#f3f4f6'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush
@endif