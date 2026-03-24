<?php

namespace App\Livewire\Admin;

use App\Models\GuardianNotification;
use App\Models\Halaqa;
use App\Models\Student;
use App\Models\StudentRequest;
use Livewire\Component;
use Livewire\WithPagination;

class StudentRequestsPage extends Component
{
    use WithPagination;

    public string $filterStatus = 'pending';

    // Approval modal
    public bool $showApproveModal   = false;
    public ?int $approvingRequestId = null;
    public ?int $approveHalaqaId    = null;

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openApprove(int $requestId): void
    {
        $this->approvingRequestId = $requestId;
        $this->approveHalaqaId    = null;
        $this->showApproveModal   = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal   = false;
        $this->approvingRequestId = null;
        $this->approveHalaqaId    = null;
    }

    public function confirmApprove(): void
    {
        $this->validate([
            'approveHalaqaId' => ['required', 'exists:halaqas,id'],
        ], [
            'approveHalaqaId.required' => 'يرجى اختيار الحلقة.',
        ]);

        $request = StudentRequest::where('id', $this->approvingRequestId)
            ->where('center_id', auth()->user()->center_id)
            ->firstOrFail();

        // Create the student in the selected halaqa
        $student = Student::create([
            'name'      => $request->student_name,
            'age'       => $request->student_age,
            'halaqa_id' => $this->approveHalaqaId,
            'is_active' => true,
        ]);

        // Mark request approved and link to the created student
        $request->update(['status' => 'approved', 'student_id' => $student->id]);

        // Create pivot in guardian_student
        $request->guardian->guardianStudents()->syncWithoutDetaching([
            $student->id => [
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ],
        ]);

        // Notify guardian
        GuardianNotification::create([
            'user_id' => $request->guardian_id,
            'type'    => 'approved',
            'message' => "تم قبول طلب تسجيل الطالب \"{$request->student_name}\" وتعيينه في حلقة.",
            'is_read' => false,
        ]);

        $this->closeApproveModal();
    }

    public function reject(int $requestId): void
    {
        $request = StudentRequest::where('id', $requestId)
            ->where('center_id', auth()->user()->center_id)
            ->firstOrFail();

        $request->update(['status' => 'rejected']);

        GuardianNotification::create([
            'user_id' => $request->guardian_id,
            'type'    => 'rejected',
            'message' => "تم رفض طلب تسجيل الطالب \"{$request->student_name}\".",
            'is_read' => false,
        ]);
    }

    public function render()
    {
        $requests = StudentRequest::where('center_id', auth()->user()->center_id)
            ->where('status', $this->filterStatus)
            ->with(['guardian', 'center'])
            ->latest()
            ->paginate(20);

        $pendingCount  = StudentRequest::where('center_id', auth()->user()->center_id)->pending()->count();
        $approvedCount = StudentRequest::where('center_id', auth()->user()->center_id)->approved()->count();
        $rejectedCount = StudentRequest::where('center_id', auth()->user()->center_id)->rejected()->count();

        $halaqas = Halaqa::where('center_id', auth()->user()->center_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.student-requests-page', compact(
            'requests', 'pendingCount', 'approvedCount', 'rejectedCount', 'halaqas'
        ))->layout('components.layouts.app', ['header' => 'طلبات التسجيل']);
    }
}
