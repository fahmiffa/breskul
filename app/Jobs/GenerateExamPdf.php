<?php

namespace App\Jobs;

use App\Models\UjianStudent;
use App\Models\Soal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateExamPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $assignmentId;

    public function __construct(int $assignmentId)
    {
        $this->assignmentId = $assignmentId;
    }

    public function handle(): void
    {
        $item = UjianStudent::with(['ujian.mapel', 'ujian.guru', 'student'])->find($this->assignmentId);

        if (!$item) return;

        $soalIds = $item->ujian->soal_id ?? [];
        $soals = Soal::whereIn('id', $soalIds)->get();
        $answers = $item->answers ?? [];

        $pdf = Pdf::loadView('master.ujian_assignment.pdf', compact('item', 'soals', 'answers'))
            ->setPaper('a4', 'portrait');

        // Simpan ke storage/app/public/exam-pdf/
        $fileName = 'exam-pdf/Evaluasi-' . $item->id . '-' . time() . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        // Update kolom pdf di database
        $item->pdf = $fileName;
        $item->save();
    }
}
