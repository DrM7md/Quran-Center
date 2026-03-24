<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\Memorization;
use Illuminate\Support\Carbon;

class ParentDashboardPage extends Component
{
    public function mount(): void
    {
        // Mark all unread notifications as read when entering dashboard
        auth()->user()
            ->guardianNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function render()
    {
        $user     = auth()->user();
        $students = $user->guardianStudents()
            ->with(['halaqa.center'])
            ->get()
            ->map(function ($student) {
                $startOfMonth = Carbon::now()->startOfMonth();

                $student->mems_this_month = $student->memorizations()
                    ->whereMonth('heard_at', now()->month)
                    ->whereYear('heard_at', now()->year)
                    ->count();

                $student->absences_count = $student->absences()
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count();

                $student->last_session = $student->memorizations()
                    ->latest('heard_at')
                    ->value('heard_at');

                return $student;
            });

        return view('livewire.parent.parent-dashboard-page', compact('students'))
            ->layout('components.layouts.parent', ['header' => 'لوحة المتابعة']);
    }
}
