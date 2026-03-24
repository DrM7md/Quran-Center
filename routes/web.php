<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Absences\AbsencesPage;
use App\Livewire\Admin\StudentRequestsPage;
use App\Livewire\Centers\CentersPage;
use App\Livewire\Centers\CenterDetailPage;
use App\Livewire\Parent\ParentDashboardPage;
use App\Livewire\Parent\ParentRequestPage;
use App\Livewire\Parent\ParentStudentDetail;
use App\Livewire\Profile\ProfilePage;
use App\Livewire\Halaqas\HalaqasPage;
use App\Livewire\Hifdh\HifdhPage;
use App\Livewire\Hifdh\ProgressPage;
use App\Livewire\Students\StudentsPage;
use App\Livewire\Users\MuhafidhsPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;



Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});


Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/profile', ProfilePage::class)
    ->middleware(['auth'])
    ->name('profile.edit');

Route::middleware(['auth', 'role:admin|muhafidh'])->group(function () {
    
// صفحات الحفظ
    Route::get('/hifdh',    HifdhPage::class)->name('hifdh.index');
    Route::get('/progress', ProgressPage::class)->name('hifdh.progress');
// صفحة الغيابات
    Route::get('/absences', \App\Livewire\Absences\AbsencesPage::class)->name('absences.index');
// صفحة الطلاب
    Route::get('/students', StudentsPage::class)->name('students.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/halaqas', HalaqasPage::class)->name('halaqas.index');
    Route::get('/muhafidhs', MuhafidhsPage::class)->name('muhafidhs.index');
});

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/centers', CentersPage::class)->name('centers.index');
    Route::get('/centers/{center}', CenterDetailPage::class)->name('centers.show');
});


// Admin - student requests management
Route::get('/student-requests', StudentRequestsPage::class)
    ->middleware(['auth', 'role:admin'])
    ->name('student-requests.index');

// Access requests (forgot-password notifications) — super-admin يرى طلبات الأدمن، admin يرى طلبات محفظيه
Volt::route('/admin/access-requests', 'admin.access-requests')
    ->middleware(['auth', 'role:super-admin|admin'])
    ->name('admin.access-requests');

// Parent portal — guest (unauthenticated) pages
Route::prefix('parent')->name('parent.')->middleware('guest')->group(function () {
    Volt::route('login',    'pages.parent.login')->name('login');
    Volt::route('register', 'pages.parent.register')->name('register');
});

// Parent portal — authenticated guardians
Route::prefix('parent')->name('parent.')->middleware(['auth', 'role:guardian'])->group(function () {
    Route::get('/',                  ParentDashboardPage::class)->name('dashboard');
    Route::get('request',            ParentRequestPage::class)->name('request');
    Route::get('student/{student}',  ParentStudentDetail::class)->name('student.show');
});

// ── Feature 4: طباعة تقرير طالب (PDF عبر المتصفح) ─────────────────────────
Route::get('/students/{student}/report', function (\App\Models\Student $student) {
    $student->load(['halaqa.center']);

    $newAyahs = \Illuminate\Support\Facades\DB::table('memorizations')
        ->where('student_id', $student->id)
        ->where('type', 'new')
        ->selectRaw('SUM(to_ayah - from_ayah + 1) as total')
        ->value('total') ?? 0;

    $reviewAyahs = \Illuminate\Support\Facades\DB::table('memorizations')
        ->where('student_id', $student->id)
        ->where('type', 'review')
        ->selectRaw('SUM(to_ayah - from_ayah + 1) as total')
        ->value('total') ?? 0;

    $sessionsCount = \App\Models\Memorization::where('student_id', $student->id)->count();

    $lastSession = \App\Models\Memorization::where('student_id', $student->id)
        ->orderByDesc('heard_at')->first();

    $surahStats = \Illuminate\Support\Facades\DB::table('memorizations')
        ->join('surahs', 'memorizations.surah_id', '=', 'surahs.id')
        ->where('memorizations.student_id', $student->id)
        ->where('memorizations.type', 'new')
        ->select('surahs.name', 'surahs.number', 'surahs.ayahs_count',
                 \Illuminate\Support\Facades\DB::raw('SUM(memorizations.to_ayah - memorizations.from_ayah + 1) as ayahs'))
        ->groupBy('surahs.id', 'surahs.name', 'surahs.number', 'surahs.ayahs_count')
        ->orderBy('surahs.number')
        ->get();

    $sessions = \App\Models\Memorization::where('student_id', $student->id)
        ->with(['surah:id,name,number', 'muhafidh:id,name'])
        ->orderByDesc('heard_at')->orderByDesc('id')
        ->limit(30)->get();

    return view('print.student-report', [
        'student'         => $student,
        'totalNewAyahs'   => (int) $newAyahs,
        'totalReviewAyahs'=> (int) $reviewAyahs,
        'sessionsCount'   => $sessionsCount,
        'lastSessionDate' => optional($lastSession?->heard_at)->format('Y/m/d'),
        'progressPct'     => min(100, round($newAyahs / 6236 * 100, 1)),
        'surahStats'      => $surahStats,
        'surahsCount'     => $surahStats->count(),
        'sessions'        => $sessions,
    ]);
})->middleware(['auth', 'role:admin|muhafidh'])->name('students.report');

// ── Feature 5: تصدير Excel (XLSX) لطلاب حلقة ────────────────────────────
Route::get('/progress/export', function (\Illuminate\Http\Request $request) {
    $user     = auth()->user();
    $halaqaId = (int) $request->query('halaqa', 0);
    $month    = now()->format('Y-m');

    $query = \App\Models\Student::query()
        ->with('halaqa:id,name')
        ->select('id', 'name', 'halaqa_id');

    if ($user->hasRole(['admin', 'super-admin'])) {
        if ($halaqaId) {
            $query->where('halaqa_id', $halaqaId);
        }
    } else {
        $ids = method_exists($user, 'accessibleHalaqaIds')
            ? $user->accessibleHalaqaIds()
            : $user->halaqas()->pluck('halaqas.id')->toArray();
        $query->whereIn('halaqa_id', $ids);
    }

    $students   = $query->orderBy('name')->get();
    $studentIds = $students->pluck('id');

    $newStats = \Illuminate\Support\Facades\DB::table('memorizations')
        ->whereIn('student_id', $studentIds)->where('type', 'new')
        ->selectRaw('student_id, SUM(to_ayah - from_ayah + 1) as total, MAX(heard_at) as last_session')
        ->groupBy('student_id')->get()->keyBy('student_id');

    $monthStats = \Illuminate\Support\Facades\DB::table('memorizations')
        ->whereIn('student_id', $studentIds)->where('type', 'new')
        ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [$month])
        ->selectRaw('student_id, SUM(to_ayah - from_ayah + 1) as month_ayahs')
        ->groupBy('student_id')->get()->keyBy('student_id');

    // ── Spreadsheet ───────────────────────────────────────────────────────
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('تقدم الطلاب');
    $sheet->setRightToLeft(true);

    $FILL   = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
    $CENTER = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
    $RIGHT  = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
    $VCEN   = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
    $THIN   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
    $MEDIUM = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;

    // ── صف العنوان (R1) ───────────────────────────────────────────────────
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', 'تقرير تقدم الحفظ — ' . now()->format('Y/m/d'));
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => $FILL, 'startColor' => ['rgb' => '1A5276']],
        'alignment' => ['horizontal' => $CENTER, 'vertical' => $VCEN],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(32);

    // ── صف الترويسة (R2) ─────────────────────────────────────────────────
    $headers = ['اسم الطالب', 'الحلقة', 'إجمالي الآيات', 'نسبة التقدم %', 'آيات هذا الشهر', 'آخر جلسة'];
    foreach (['A','B','C','D','E','F'] as $i => $col) {
        $sheet->setCellValue($col . '2', $headers[$i]);
    }
    $sheet->getStyle('A2:F2')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => $FILL, 'startColor' => ['rgb' => '1E8449']],
        'alignment' => ['horizontal' => $CENTER, 'vertical' => $VCEN],
        'borders'   => ['allBorders' => ['borderStyle' => $THIN, 'color' => ['rgb' => '145A32']]],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(24);
    $sheet->freezePane('A3');

    // ── صفوف البيانات ─────────────────────────────────────────────────────
    $row = 3;
    foreach ($students as $s) {
        $total    = (int) ($newStats[$s->id]->total         ?? 0);
        $monthly  = (int) ($monthStats[$s->id]->month_ayahs ?? 0);
        $lastDate = $newStats[$s->id]->last_session ?? '';
        $pct      = $total > 0 ? round($total / 6236 * 100, 1) : 0;

        $sheet->setCellValue('A' . $row, $s->name);
        $sheet->setCellValue('B' . $row, $s->halaqa?->name ?? '—');
        $sheet->setCellValue('C' . $row, $total);
        $sheet->setCellValue('D' . $row, $pct);
        $sheet->setCellValue('E' . $row, $monthly);
        $sheet->setCellValue('F' . $row, $lastDate ?: '—');

        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.0"%"');
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // ألوان متناوبة
        $bg = ($row % 2 === 0) ? 'EAFAF1' : 'FFFFFF';
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'fill'      => ['fillType' => $FILL, 'startColor' => ['rgb' => $bg]],
            'alignment' => ['horizontal' => $CENTER, 'vertical' => $VCEN],
            'borders'   => ['allBorders' => ['borderStyle' => $THIN, 'color' => ['rgb' => 'D5D8DC']]],
        ]);

        // اسم الطالب يُحاذى يميناً
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal($RIGHT);

        // لون نسبة التقدم
        $pctColor = $pct >= 50 ? '1E8449' : ($pct > 0 ? 'D35400' : '808080');
        $sheet->getStyle('D' . $row)->getFont()->getColor()->setRGB($pctColor);
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);

        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;
    }

    // ── صف المجموع ────────────────────────────────────────────────────────
    if ($students->isNotEmpty()) {
        $sumTotal = $students->sum(fn($s) => (int) ($newStats[$s->id]->total ?? 0));
        $sumMonth = $students->sum(fn($s) => (int) ($monthStats[$s->id]->month_ayahs ?? 0));

        $sheet->setCellValue('A' . $row, 'المجموع');
        $sheet->setCellValue('B' . $row, $students->count() . ' طالب');
        $sheet->setCellValue('C' . $row, $sumTotal);
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, $sumMonth);
        $sheet->setCellValue('F' . $row, '');
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'fill'      => ['fillType' => $FILL, 'startColor' => ['rgb' => 'D6EAF8']],
            'alignment' => ['horizontal' => $CENTER, 'vertical' => $VCEN],
            'borders'   => ['allBorders' => ['borderStyle' => $THIN,   'color' => ['rgb' => '2E86C1']],
                            'outline'    => ['borderStyle' => $MEDIUM, 'color' => ['rgb' => '1A5276']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    // ── إطار خارجي + عرض الأعمدة ─────────────────────────────────────────
    $lastRow = $row;
    $sheet->getStyle("A1:F{$lastRow}")->applyFromArray([
        'borders' => ['outline' => ['borderStyle' => $MEDIUM, 'color' => ['rgb' => '1A5276']]],
    ]);

    foreach (['A' => 28, 'B' => 22, 'C' => 16, 'D' => 16, 'E' => 18, 'F' => 16] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    // ── إرسال الملف ───────────────────────────────────────────────────────
    $filename = 'تقدم-الطلاب-' . now()->format('Y-m-d') . '.xlsx';
    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Cache-Control'       => 'max-age=0',
        'Content-Disposition' => 'attachment',
    ]);
})->middleware(['auth', 'role:admin|muhafidh'])->name('progress.export');

// ── Quran text — يقرأ من قاعدة البيانات (دائم، بدون إنترنت) ───────────────
// شغّل مرة واحدة فقط: php artisan db:seed --class=AyahsSeeder
// ?student=ID  →  يُرجع أيضًا الآيات المحفوظة سابقًا للطالب في هذه السورة
Route::get('/quran/surah/{number}', function (\Illuminate\Http\Request $request, int $number) {
    abort_if($number < 1 || $number > 114, 404);

    $ayahs = \Illuminate\Support\Facades\DB::table('ayahs')
        ->where('surah_number', $number)
        ->orderBy('number_in_surah')
        ->select('number_in_surah as n', 'text as t')
        ->get();

    // ── الآيات المحفوظة سابقًا للطالب في هذه السورة ──
    $memorized = [];

    if ($studentId = (int) $request->query('student', 0)) {
        $surahId = \Illuminate\Support\Facades\DB::table('surahs')
            ->where('number', $number)
            ->value('id');

        if ($surahId) {
            $ranges = \Illuminate\Support\Facades\DB::table('memorizations')
                ->where('student_id', $studentId)
                ->where('surah_id', $surahId)
                ->where('type', 'new')
                ->select('from_ayah', 'to_ayah')
                ->get();

            // بناء مجموعة مسطّحة من أرقام الآيات المحفوظة
            $set = [];
            foreach ($ranges as $r) {
                for ($i = $r->from_ayah; $i <= $r->to_ayah; $i++) {
                    $set[$i] = true;
                }
            }
            $memorized = array_keys($set);
        }
    }

    return response()->json(['ayahs' => $ayahs, 'memorized' => $memorized]);
})->middleware('auth')->name('quran.surah');

require __DIR__.'/auth.php';


