<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = trim((string) $request->query('role', ''));
        $modal = trim((string) $request->query('modal', ''));
        $modalUser = null;

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, [User::ROLE_ADMIN, User::ROLE_AUTHOR, User::ROLE_OPERATOR, User::ROLE_PESERTA], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        if ($modal === 'edit') {
            $modalUserId = (int) $request->query('user');
            if ($modalUserId > 0) {
                $modalUser = User::query()
                    ->select('id', 'name', 'email', 'role')
                    ->find($modalUserId);
            }
        }

        return view('admin.users.index', compact('users', 'search', 'role', 'modal', 'modalUser'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.users.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_AUTHOR, User::ROLE_OPERATOR, User::ROLE_PESERTA])],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'user-created');
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
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_AUTHOR, User::ROLE_OPERATOR, User::ROLE_PESERTA])],
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

        return redirect()->route('admin.users.index')->with('status', 'user-updated');
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

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }
}
