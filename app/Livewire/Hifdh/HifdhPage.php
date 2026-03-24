<?php

namespace App\Livewire\Hifdh;

use App\Models\Halaqa;
use App\Models\Memorization;
use App\Models\Student;
use App\Models\Surah;
use Livewire\Component;

class HifdhPage extends Component
{
    // للأدمن: يختار حلقة ثم طالب
    public ?int $halaqa_id = null;

    // للكل
    public ?int $student_id = null;
    public string $type = 'new'; // new | review

    // بحث السور
    public string $surah_search    = '';
    public ?int $surah_id          = null;
    public array $surah_results    = [];
    public array $selectedSurahData = []; // name, number, ayahs_count للسورة المختارة

    // Dropdown الطلاب (نجهزها كـ array جاهزة للـ blade)
    public array $students = [];

    public ?int $from_ayah = null;
    public ?int $to_ayah = null;

    public string $rating = 'excellent';
    public ?string $notes = null;
    public string $heard_at;

    // مودال السجل كاملًا
    public bool $showAllHistoryModal = false;
    public array $allHistory = [];
    public int $allHistoryTake = 50;
    public bool $hasMoreAllHistory = false;

    // حذف تسميع
    public bool $showDeleteMemModal = false;
    public ?int $deleteMemId = null;
    public ?string $deleteMemTitle = null;

    // سجل الطالب (مختصر)
    public array $history = [];

    // Feature 1: آخر جلسة في السورة المختارة (اقتراح الاستمرار)
    public array $lastSession = [];

    // Feature 2: عدد الأيام منذ آخر تسميع (أي نوع، أي سورة)
    public ?int $daysSinceLastSession = null;

    public function mount(): void
    {
        $this->heard_at = now()->toDateString();

        // ✅ جهز الطلاب أول ما تفتح الصفحة
        $this->loadStudents();
    }

    public function render()
    {
        $user = auth()->user();

        // ✅ الحلقات
        if ($this->isAdmin()) {
            $authUser = auth()->user();
            $halaqas = Halaqa::query()
                ->select('id', 'name')
                ->when($authUser->isCenterAdmin() && $authUser->center_id, fn($q) => $q->where('center_id', $authUser->center_id))
                ->orderBy('name')
                ->get();
        } else {
            // ✅ للمحفّظ: فقط الحلقات اللي له صلاحية عليها (أساسي + تغطية) مع مراعاة starts/ends
            $ids = $this->accessibleHalaqaIds();

            $halaqas = empty($ids)
                ? collect()
                : Halaqa::query()
                    ->select('id', 'name')
                    ->whereIn('id', $ids)
                    ->orderBy('name')
                    ->get();
        }

        // ✅ الطلاب نرسلهم من $this->students (بدون Query ثاني)
        $students = $this->students;

        return view('livewire.hifdh.hifdh-page', compact('halaqas', 'students'))
            ->layout('components.layouts.app', ['header' => 'تسجيل التسميع']);
    }

    // =========================
    // Events
    // =========================

    public function updatedHalaqaId(): void
    {
        // بس الأدمن يغيّر الحلقة من dropdown
        if ($this->isAdmin()) {
            $this->student_id = null;
            $this->history = [];
            $this->closeAllHistoryModal();
            $this->loadStudents();
        }
    }

    public function updatedStudentId(): void
    {
        $this->lastSession = [];
        $this->loadHistory();
        $this->loadLastSession();

        if ($this->showAllHistoryModal) {
            $this->loadAllHistory();
        }
    }

    public function updatedSurahSearch($value): void
    {
        $txt = trim((string) $value);

        if ($txt === '') {
            $this->surah_results = [];
            $this->surah_id = null;
            return;
        }

        $results = Surah::query()
            ->select('id', 'name', 'number', 'ayahs_count')
            ->where(function ($q) use ($txt) {
                $q->where('name', 'like', "%{$txt}%");

                if (is_numeric($txt)) {
                    $q->orWhere('number', (int) $txt);
                }
            })
            ->orderBy('number')
            ->limit(20)
            ->get();

        $this->surah_results = $results->toArray();

        // تثبيت surah_id فقط إذا الاسم مطابق تمامًا
        $exact = $results->firstWhere('name', $txt);

        if ($exact) {
            $this->surah_id   = (int) $exact->id;
            $this->from_ayah  = $this->from_ayah ?: 1;
            $this->to_ayah    = $this->to_ayah   ?: min(5, (int) $exact->ayahs_count);
            $this->selectedSurahData = [
                'name'        => $exact->name,
                'number'      => (int) $exact->number,
                'ayahs_count' => (int) $exact->ayahs_count,
            ];
            $this->loadLastSession();
        } else {
            $this->surah_id          = null;
            $this->selectedSurahData = [];
            $this->lastSession       = [];
        }
    }

    // =========================
    // Loaders
    // =========================

    private function loadStudents(): void
    {
        $user = auth()->user();

        $q = Student::query()
            ->select('id', 'name', 'halaqa_id')
            ->with(['halaqa:id,name'])
            ->orderBy('name');

        if ($this->isAdmin()) {
            if ($this->halaqa_id) {
                $q->where('halaqa_id', (int) $this->halaqa_id);
            } else {
                $q->whereRaw('1=0');
            }
        } else {
            // ✅ هنا بيت الحل: طلاب كل الحلقات اللي يغطيها المحفّظ
            $ids = $this->accessibleHalaqaIds();

            if (empty($ids)) {
                $q->whereRaw('1=0');
            } else {
                $q->whereIn('halaqa_id', $ids);
            }
        }

        $this->students = $q->get()
            ->map(fn ($s) => [
                'id'     => (int) $s->id,
                'name'   => $s->name,
                'halaqa' => $s->halaqa?->name, // ✅ عشان تقدر تميّز الطالب إذا تكرر الاسم
            ])
            ->toArray();
    }

    private function loadHistory(): void
    {
        $this->history              = [];
        $this->daysSinceLastSession = null;
        if (!$this->student_id) return;

        $this->assertStudentVisible((int) $this->student_id);

        $rows = Memorization::query()
            ->where('student_id', (int) $this->student_id)
            ->with(['surah:id,name,number', 'muhafidh:id,name'])
            ->orderByDesc('heard_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $this->history = $rows->map(fn ($r) => [
            'id'       => (int) $r->id,
            'date'     => optional($r->heard_at)->format('Y-m-d'),
            'type'     => $r->type,
            'surah'    => $r->surah?->name,
            'range'    => $r->from_ayah . ' - ' . $r->to_ayah,
            'rating'   => $r->rating,
            'muhafidh' => $r->muhafidh?->name,
            'notes'    => $r->notes,
        ])->toArray();

        // Feature 2: كم يوم مضى على آخر جلسة؟
        $lastDate = $this->history[0]['date'] ?? null;
        if ($lastDate) {
            $this->daysSinceLastSession = (int) now()->diffInDays($lastDate);
        }
    }

    // Feature 1: آخر جلسة حفظ جديد للطالب في السورة المختارة
    private function loadLastSession(): void
    {
        $this->lastSession = [];
        if (!$this->student_id || !$this->surah_id) return;

        $last = Memorization::query()
            ->where('student_id', $this->student_id)
            ->where('surah_id', $this->surah_id)
            ->where('type', 'new')
            ->orderByDesc('heard_at')
            ->orderByDesc('id')
            ->first();

        if (!$last) return;

        $surah    = Surah::query()->select('id', 'ayahs_count')->find($this->surah_id);
        $nextFrom = (int) $last->to_ayah + 1;
        $finished = !$surah || $nextFrom > (int) $surah->ayahs_count;

        $this->lastSession = [
            'last_from' => (int) $last->from_ayah,
            'last_to'   => (int) $last->to_ayah,
            'date'      => optional($last->heard_at)->format('Y-m-d'),
            'days_ago'  => (int) now()->diffInDays($last->heard_at),
            'finished'  => $finished,
            'next_from' => $finished ? null : $nextFrom,
            'next_to'   => $finished ? null : min($nextFrom + 9, (int) $surah->ayahs_count),
        ];
    }

    // Feature 1: تطبيق الاقتراح (يملأ حقلي from/to)
    public function applySuggestion(): void
    {
        if (empty($this->lastSession) || $this->lastSession['finished']) {
            return;
        }
        $this->from_ayah = $this->lastSession['next_from'];
        $this->to_ayah   = $this->lastSession['next_to'];
    }

    // =========================
    // Save
    // =========================

    public function save(): void
    {
        $user = auth()->user();

        // ✅ قواعد حسب الدور
        $rules = [
            'student_id' => ['required', 'exists:students,id'],
            'type'       => ['required', 'in:new,review'],
            'surah_id'   => ['required', 'exists:surahs,id'],
            'from_ayah'  => ['required', 'integer', 'min:1'],
            'to_ayah'    => ['required', 'integer', 'min:1'],
            'rating'     => ['required', 'in:excellent,very_good,good,weak,repeat'],
            'notes'      => ['nullable', 'string', 'max:2000'],
            'heard_at'   => ['required', 'date'],
        ];

        // ✅ الأدمن فقط لازم يحدد halaqa_id
        if ($this->isAdmin()) {
            $rules['halaqa_id'] = ['required', 'exists:halaqas,id'];
        } else {
            $rules['halaqa_id'] = ['nullable']; // ما نعتمد عليها أصلاً
        }

        $data = $this->validate($rules, [
            'student_id.required' => 'اختر الطالب.',
            'surah_id.required'   => 'اختر السورة من القائمة.',
            'halaqa_id.required'  => 'اختر الحلقة.',
        ]);

        // ✅ نجيب الطالب ونتأكد من صلاحية المحفّظ عليه (أساسي + تغطية)
        $student = Student::query()
            ->select('id', 'halaqa_id')
            ->findOrFail((int) $data['student_id']);

        $this->assertStudentVisible((int) $student->id);

        // ✅ للمحفّظ: halaqa_id ياخذها من الطالب نفسه (حلقة التغطية/الأساسية)
        if (!$this->isAdmin()) {
            $data['halaqa_id'] = (int) $student->halaqa_id;
        } else {
            // ✅ للأدمن: لازم الطالب يكون من نفس الحلقة المختارة
            abort_if((int) $student->halaqa_id !== (int) $data['halaqa_id'], 403);
        }

        // ✅ تحقق الآيات
        $surah = Surah::query()
            ->select('id', 'ayahs_count')
            ->findOrFail((int) $data['surah_id']);

        abort_if((int) $data['from_ayah'] > (int) $data['to_ayah'], 422, 'ترتيب الآيات غير صحيح.');
        abort_if((int) $data['to_ayah'] > (int) $surah->ayahs_count, 422, 'رقم الآية أكبر من عدد آيات السورة.');

        Memorization::create([
            ...$data,
            'muhafidh_id' => (int) $user->id,
        ]);

        session()->flash('success', 'تم حفظ التسميع بنجاح ✅');

        // تفريغ حقول التسميع فقط — نبقي student_id كما هو
        $this->reset([
            'surah_search',
            'surah_id',
            'surah_results',
            'selectedSurahData',
            'from_ayah',
            'to_ayah',
            'notes',
        ]);

        $this->type    = 'new';
        $this->rating  = 'excellent';
        $this->heard_at = now()->toDateString();

        // تحديث سجل الطالب فوراً
        $this->loadHistory();
    }

    // =========================
    // All history modal
    // =========================

    public function openAllHistory(): void
    {
        if (!$this->student_id) return;

        $this->allHistoryTake = 50;
        $this->showAllHistoryModal = true;
        $this->loadAllHistory();
    }

    public function loadMoreAllHistory(): void
    {
        $this->allHistoryTake += 50;
        $this->loadAllHistory();
    }

    public function closeAllHistoryModal(): void
    {
        $this->showAllHistoryModal = false;
        $this->allHistory = [];
        $this->allHistoryTake = 50;
        $this->hasMoreAllHistory = false;
    }

    private function loadAllHistory(): void
    {
        if (!$this->student_id) {
            $this->allHistory = [];
            $this->hasMoreAllHistory = false;
            return;
        }

        $this->assertStudentVisible((int) $this->student_id);

        $rows = Memorization::query()
            ->where('student_id', (int) $this->student_id)
            ->with(['surah:id,name,number', 'muhafidh:id,name'])
            ->orderByDesc('heard_at')
            ->orderByDesc('id')
            ->limit($this->allHistoryTake + 1)
            ->get();

        $this->hasMoreAllHistory = $rows->count() > $this->allHistoryTake;
        $rows = $rows->take($this->allHistoryTake);

        $this->allHistory = $rows->map(fn ($r) => [
            'id'       => (int) $r->id,
            'date'     => optional($r->heard_at)->format('Y-m-d'),
            'type'     => $r->type,
            'surah'    => $r->surah?->name,
            'range'    => $r->from_ayah . ' - ' . $r->to_ayah,
            'rating'   => $r->rating,
            'muhafidh' => $r->muhafidh?->name,
            'notes'    => $r->notes,
        ])->toArray();
    }

    // =========================
    // Delete
    // =========================

    public function confirmDeleteMem(int $id): void
    {
        $m = Memorization::query()->with('surah:id,name')->findOrFail($id);

        $this->authorizeDeleteMem($m);

        $this->deleteMemId = (int) $m->id;
        $this->deleteMemTitle = ($m->surah?->name ?? 'سجل') . " ({$m->from_ayah} - {$m->to_ayah})";
        $this->showDeleteMemModal = true;
    }

    public function closeDeleteMemModal(): void
    {
        $this->showDeleteMemModal = false;
        $this->deleteMemId = null;
        $this->deleteMemTitle = null;
    }

    public function deleteMem(): void
    {
        if (!$this->deleteMemId) return;

        $m = Memorization::query()->findOrFail((int) $this->deleteMemId);

        $this->authorizeDeleteMem($m);

        $m->delete();

        $this->closeDeleteMemModal();
        $this->loadHistory();

        session()->flash('success', 'تم حذف التسميع ✅');
    }

    // =========================
    // Helpers (الصلاحيات)
    // =========================

    private function accessibleHalaqaIds(): array
    {
        $user = auth()->user();
        if (!$user) return [];

        // الأدمن ما نقيّده
        if ($this->isAdmin()) return [];

        // ✅ عندك جاهزة في User Model
        return method_exists($user, 'accessibleHalaqaIds')
            ? $user->accessibleHalaqaIds()
            : $user->halaqas()->pluck('halaqas.id')->map(fn ($v) => (int) $v)->toArray();
    }

    private function assertStudentVisible(int $studentId): void
    {
        if ($this->isAdmin()) return;

        $ids = $this->accessibleHalaqaIds();
        if (empty($ids)) abort(403);

        $st = Student::query()->select('id', 'halaqa_id')->find($studentId);
        if (!$st) abort(403);

        abort_if(!in_array((int) $st->halaqa_id, $ids, true), 403);
    }

    private function authorizeDeleteMem(Memorization $m): void
    {
        if ($this->isAdmin()) return;

        // ✅ المحفّظ يقدر يحذف لأي طالب ضمن الحلقات اللي ماسكها (أساسي/تغطية)
        $this->assertStudentVisible((int) $m->student_id);
    }

    private function isAdmin(): bool
    {
        return auth()->user()?->hasRole(['admin', 'super-admin']) ?? false;
    }
}
