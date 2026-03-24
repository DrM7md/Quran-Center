<?php

namespace App\Livewire\Parent;

use App\Models\Center;
use App\Models\StudentRequest;
use Livewire\Component;

class ParentRequestPage extends Component
{
    public ?int $selectedCenter  = null;
    public string $guardianPhone = '';
    public string $studentName   = '';
    public ?int $studentAge      = null;
    public string $studentNotes  = '';

    public function submitRequest(): void
    {
        $this->validate([
            'selectedCenter' => ['required', 'exists:centers,id'],
            'guardianPhone'  => ['required', 'string', 'max:20'],
            'studentName'    => ['required', 'string', 'max:255'],
            'studentAge'     => ['nullable', 'integer', 'min:4', 'max:99'],
            'studentNotes'   => ['nullable', 'string', 'max:500'],
        ], [
            'selectedCenter.required' => 'يرجى اختيار المركز.',
            'guardianPhone.required'  => 'يرجى إدخال رقم الهاتف.',
            'studentName.required'    => 'يرجى إدخال اسم الطالب.',
            'studentAge.min'          => 'العمر يجب أن يكون 4 سنوات على الأقل.',
            'studentAge.max'          => 'العمر يبدو غير صحيح.',
        ]);

        StudentRequest::create([
            'guardian_id'    => auth()->id(),
            'guardian_phone' => trim($this->guardianPhone),
            'center_id'      => $this->selectedCenter,
            'student_name'   => trim($this->studentName),
            'student_age'    => $this->studentAge,
            'student_notes'  => $this->studentNotes ?: null,
            'status'         => 'pending',
        ]);

        $this->reset(['selectedCenter', 'guardianPhone', 'studentName', 'studentAge', 'studentNotes']);

        session()->flash('success', 'تم إرسال طلب التسجيل بنجاح. سيتم إعلامك بالنتيجة قريباً.');
    }

    public function render()
    {
        $centers = Center::where('is_active', true)->orderBy('name')->get();

        $myRequests = StudentRequest::where('guardian_id', auth()->id())
            ->with(['center'])
            ->latest()
            ->get();

        return view('livewire.parent.parent-request-page', compact('centers', 'myRequests'))
            ->layout('components.layouts.parent', ['header' => 'تسجيل طالب']);
    }
}
