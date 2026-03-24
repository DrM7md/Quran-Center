<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class ProfilePage extends Component
{
    // --- معلومات الحساب ---
    public string $name  = '';
    public string $email = '';

    // --- تغيير كلمة المرور ---
    public string $current_password  = '';
    public string $new_password      = '';
    public string $confirm_password  = '';

    public bool $profileSaved  = false;
    public bool $passwordSaved = false;

    public function mount(): void
    {
        $this->name  = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function saveProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'  => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'البريد الإلكتروني غير صالح.',
            'email.unique'   => 'هذا البريد الإلكتروني مستخدم من قِبل حساب آخر.',
        ]);

        $user->name  = $this->name;
        $user->email = $this->email;
        $user->save();

        $this->profileSaved = true;
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required'],
            'new_password'     => ['required', Password::min(8), 'different:current_password'],
            'confirm_password' => ['required', 'same:new_password'],
        ], [
            'current_password.required' => 'أدخل كلمة المرور الحالية.',
            'new_password.required'     => 'أدخل كلمة المرور الجديدة.',
            'new_password.min'          => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'new_password.different'    => 'كلمة المرور الجديدة يجب أن تختلف عن الحالية.',
            'confirm_password.required' => 'تأكيد كلمة المرور مطلوب.',
            'confirm_password.same'     => 'تأكيد كلمة المرور لا يطابق الكلمة الجديدة.',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'كلمة المرور الحالية غير صحيحة.');
            return;
        }

        Auth::user()->update(['password' => Hash::make($this->new_password)]);

        $this->current_password = '';
        $this->new_password     = '';
        $this->confirm_password = '';
        $this->passwordSaved    = true;
    }

    public function render()
    {
        return view('livewire.profile.profile-page')
            ->layout('components.layouts.app', ['header' => 'الملف الشخصي']);
    }
}
