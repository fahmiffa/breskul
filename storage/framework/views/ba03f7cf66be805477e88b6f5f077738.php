<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Evaluasi Hasil Ujian</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #177245;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #177245;
            margin: 0;
            text-transform: uppercase;
            font-size: 18px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 8px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            width: 120px;
        }

        .score-box {
            background-color: #f0fff4;
            border: 1px solid #177245;
            padding: 8px;
            text-align: center;
            border-radius: 5px;
            float: right;
            width: 80px;
        }

        .score-value {
            font-size: 18px;
            font-weight: bold;
            color: #177245;
            display: block;
        }

        .question-card {
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 8px;
            page-break-inside: avoid;
        }

        .question-header {
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .question-number {
            font-weight: bold;
            background: #333;
            color: #fff;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            float: right;
        }

        .status-benar {
            color: #059669;
        }

        .status-salah {
            color: #dc2626;
        }

        .answer-grid {
            width: 100%;
            margin-top: 5px;
        }

        .answer-box {
            padding: 5px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .answer-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
        }

        .answer-text {
            font-weight: bold;
            font-size: 10px;
        }

        .question-text {
            margin-bottom: 5px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Evaluasi Hasil Ujian</h1>
    </div>

    <div class="info-section">
        <div class="score-box">
            <span class="score-value"><?php echo e($item->score); ?></span>
            <span style="font-size: 10px;">SKOR TOTAL</span>
        </div>
        <table class="info-table">
            <tr>
                <td class="info-label">Nama Murid</td>
                <td>: <?php echo e($item->student->name); ?></td>
            </tr>
            <tr>
                <td class="info-label">Nama Ujian</td>
                <td>: <?php echo e($item->ujian->nama); ?></td>
            </tr>
            <tr>
                <td class="info-label">Mata Pelajaran</td>
                <td>: <?php echo e($item->ujian->mapel->name ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Guru Pengampu</td>
                <td>: <?php echo e($item->ujian->guru->name ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Tanggal Selesai</td>
                <td>: <?php echo e($item->finished_at ? \Carbon\Carbon::parse($item->finished_at)->format('d M Y, H:i') : '-'); ?></td>
            </tr>
        </table>
    </div>

    <div style="clear: both; margin-top: 40px;">
        <h3>Detail Pengerjaan</h3>

        <?php
        $stripHtml = function($html) {
        return strip_tags(str_replace(['<br>', '<br />', '<p>', '
    </div>'], ["\n", "\n", "\n", "\n"], $html));
    };
    ?>

    <?php $__currentLoopData = $soals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $soal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
    $studentKey = strtoupper(trim($answers[$soal->id] ?? '-'));
    $correctValue = trim(strip_tags($soal->jawaban));

    $studentValue = $studentKey;
    if (in_array($studentKey, ['A', 'B', 'C', 'D', 'E'])) {
    $optKey = 'opsi_' . strtolower($studentKey);
    $studentValue = $soal->$optKey ? strip_tags($soal->$optKey) : $studentKey;
    }

    $isCorrect = (trim(strip_tags($studentValue)) == $correctValue) || ($studentKey == strtoupper($correctValue));
    ?>

    <div class="question-card">
        <div class="question-header">
            <span class="question-number">SOAL <?php echo e($index + 1); ?></span>
            <span class="status <?php echo e($isCorrect ? 'status-benar' : 'status-salah'); ?>">
                <?php echo e($isCorrect ? '✓ Benar' : '✗ Salah'); ?>

            </span>
        </div>

        <div class="question-text">
            <?php echo $soal->nama; ?>

        </div>

        <table style="width: 100%; margin-top: 5px; border-spacing: 5px 0;">
            <tr>
                <td style="width: 50%; padding: 0;">
                    <div class="answer-box">
                        <span class="answer-label">Murid:</span>
                        <span class="answer-text"><?php echo e($studentKey); ?> - <?php echo e($studentValue); ?></span>
                    </div>
                </td>
                <td style="width: 50%; padding: 0;">
                    <div class="answer-box">
                        <span class="answer-label">Kunci:</span>
                        <span class="answer-text" style="color: #059669;"><?php echo e($correctValue); ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div style="margin-top: 50px; text-align: right; font-size: 12px;">
        <p>Dicetak pada: <?php echo e(now()->format('d M Y, H:i')); ?></p>
    </div>
</body>

</html><?php /**PATH E:\project\breskul\web\resources\views/master/ujian_assignment/pdf.blade.php ENDPATH**/ ?>