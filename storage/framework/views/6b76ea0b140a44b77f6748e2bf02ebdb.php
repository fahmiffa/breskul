<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="mb-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <form action="<?php echo e(route('dashboard.home')); ?>" method="GET">
        <select name="year" onchange="this.form.submit()" class="border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 p-2 cursor-pointer">
            <?php for($y = date('Y'); $y >= date('Y')-2; $y--): ?>
            <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Kelas</div>
        <div class="text-2xl font-bold text-green-600"><?php echo e($totalKelas); ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Mapel</div>
        <div class="text-2xl font-bold text-blue-600"><?php echo e($totalMapel); ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Guru</div>
        <div class="text-2xl font-bold text-purple-600"><?php echo e($totalGuru); ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Murid</div>
        <div class="text-2xl font-bold text-orange-600"><?php echo e($totalMurid); ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 flex flex-col items-center justify-center">
        <div class="text-gray-500 text-sm font-medium">Total Ekskul</div>
        <div class="text-2xl font-bold text-red-600"><?php echo e($totalEkskul); ?></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Payment Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Grafik Pembayaran (<?php echo e($year); ?>)</h3>
        <canvas id="paymentChart"></canvas>
    </div>

    <!-- Attendance Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Grafik Absensi (<?php echo e(date('F Y')); ?>)</h3>
        <canvas id="attendanceChart"></canvas>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
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
                    data: <?php echo json_encode($paymentData ?? [], 15, 512) ?>,
                    backgroundColor: 'rgba(22, 163, 74, 0.7)', // Green-600
                    borderColor: 'rgba(22, 163, 74, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.5,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Total Tagihan (Rp)',
                    data: <?php echo json_encode($unpaidPaymentData ?? [], 15, 512) ?>,
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
            labels: <?php echo json_encode($attendanceLabels ?? [], 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Kehadiran',
                data: <?php echo json_encode($attendanceData ?? [], 15, 512) ?>,
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('base.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/franken/breskull/resources/views/home/index.blade.php ENDPATH**/ ?>