<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UserAccountsExport;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AdminUserController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.users.students.index');
    }

    public function students(): View
    {
        return $this->renderByRole(User::ROLE_STUDENT);
    }

    public function teachers(): View
    {
        return $this->renderByRole(User::ROLE_TEACHER);
    }

    public function operators(): View
    {
        return $this->renderByRole(User::ROLE_OPERATOR);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.users.teachers.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_TEACHER, User::ROLE_OPERATOR])],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        User::create($data);

        return back()->with('status', 'user-created');
    }

    public function edit(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.index', [
            'modal' => 'edit',
            'user' => $user->id,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_TEACHER, User::ROLE_OPERATOR])],
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (
            $user->role === User::ROLE_ADMIN
            && $data['role'] !== User::ROLE_ADMIN
            && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1
        ) {
            return back()->withErrors(['role' => 'Minimal harus ada 1 admin aktif.'])->withInput();
        }

        $user->update($data);

        return back()->with('status', 'user-updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->withErrors(['delete' => 'Akun sendiri tidak boleh dihapus.']);
        }

        if ($user->role === User::ROLE_ADMIN && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->withErrors(['delete' => 'Minimal harus ada 1 admin aktif.']);
        }

        $user->delete();

        return back()->with('status', 'user-deleted');
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR, User::ROLE_TEACHER, User::ROLE_STUDENT])],
            'class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'format' => ['nullable', Rule::in(['xlsx', 'csv'])],
        ]);

        $role = (string) $data['role'];
        $classId = isset($data['class_id']) ? (int) $data['class_id'] : null;
        $format = (string) ($data['format'] ?? 'xlsx');

        if ($role === User::ROLE_STUDENT && ! $classId) {
            return back()->withErrors(['export' => 'Export role student wajib pilih kelas.'])->withInput();
        }

        $query = User::query()
            ->with('schoolClass:id,name')
            ->where('role', $role)
            ->orderBy('name');

        $className = null;
        if ($role === User::ROLE_STUDENT && $classId) {
            $query->where('class_id', $classId);
            $className = SchoolClass::query()->whereKey($classId)->value('name');
        }

        $rows = $query->get([
            'id', 'name', 'email', 'role', 'class_id',
            'nis', 'nisn', 'nik', 'birth_place', 'birth_date', 'guardian_name',
        ]);

        $safeRole = str_replace(' ', '-', strtolower($role));
        $filename = 'akun-'.$safeRole;
        if ($className) {
            $filename .= '-'.str_replace(' ', '-', strtolower((string) $className));
        }
        $filename .= '-'.now()->format('Ymd_His').'.'.$format;

        if ($format === 'csv') {
            return Excel::download(new UserAccountsExport($rows), $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new UserAccountsExport($rows), $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    protected function renderByRole(string $scopeRole): View
    {
        return view('admin.users.index', [
            'scopeRole' => $scopeRole,
        ]);
    }
}
