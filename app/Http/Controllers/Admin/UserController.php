<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'permissionOptions' => User::adminPermissionOptions(),
            'canManageRoles' => auth()->user()?->isOwner() ?? false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $hasPermissionsColumn = Schema::hasColumn('users', 'admin_permissions');

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bio' => 'nullable|string|max:1000',
            'task' => 'nullable|string|max:255',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
        ];

        if ($hasRoleColumn) {
            $rules['role'] = ['nullable', Rule::in([User::ROLE_OWNER, User::ROLE_ADMIN, User::ROLE_MODERATOR])];
        }
        if ($hasPermissionsColumn) {
            $rules['admin_permissions'] = 'nullable|array';
            $rules['admin_permissions.*'] = ['string', Rule::in(array_keys(User::adminPermissionOptions()))];
        }

        $request->validate($rules);

        $data = $request->except(['avatar', 'is_admin', 'is_banned', 'role', 'admin_permissions']);

        if (Schema::hasColumn('users', 'avatar') && $request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        foreach (['task', 'bio'] as $column) {
            if (! Schema::hasColumn('users', $column)) {
                unset($data[$column]);
            }
        }

        if (! Schema::hasColumn('users', 'avatar')) {
            unset($data['avatar']);
        }

        $authUser = auth()->user();

        if ($user->id !== $authUser?->id) {
            $data['is_banned'] = $request->boolean('is_banned') && ! $user->isOwner();
        }

        if (($authUser?->isOwner() ?? false) && $user->id !== $authUser?->id && $hasRoleColumn && $hasPermissionsColumn) {
            $role = (string) $request->input('role', User::ROLE_MODERATOR);
            $permissions = array_values(array_unique(array_filter((array) $request->input('admin_permissions', []))));

            if ($role === User::ROLE_OWNER) {
                $permissions = ['*'];
            }
            if (in_array($role, [User::ROLE_ADMIN, User::ROLE_MODERATOR], true)) {
                // Avoid accidental lockout and expose key admin entries by default.
                foreach (['dashboard', 'pages'] as $minimumPermission) {
                    if (! in_array($minimumPermission, $permissions, true)) {
                        $permissions[] = $minimumPermission;
                    }
                }
            }

            $data['role'] = $role;
            $data['is_admin'] = in_array($role, [User::ROLE_OWNER, User::ROLE_ADMIN, User::ROLE_MODERATOR], true);
            $data['admin_permissions'] = $permissions;
        } elseif ($user->id !== $authUser?->id && (! $hasRoleColumn || ! $user->isOwner())) {
            $data['is_admin'] = $request->boolean('is_admin');
            if (! $data['is_admin'] && $hasRoleColumn && $hasPermissionsColumn) {
                $data['role'] = null;
                $data['admin_permissions'] = null;
            }
        }

        $before = [
            'is_admin' => $user->is_admin,
            'is_banned' => $user->is_banned,
            'email' => $user->email,
        ];
        if ($hasRoleColumn) {
            $before['role'] = $user->role;
        }
        if ($hasPermissionsColumn) {
            $before['admin_permissions'] = $user->admin_permissions;
        }

        $user->update($data);

        ActivityLogger::log(
            'user.updated',
            $user->name,
            $user,
            ['before' => $before, 'after' => array_filter([
                'is_admin' => $user->is_admin,
                'role' => $hasRoleColumn ? $user->role : null,
                'admin_permissions' => $hasPermissionsColumn ? $user->admin_permissions : null,
                'is_banned' => $user->is_banned,
                'email' => $user->email,
            ], static fn ($v) => $v !== null)]
        );

        return redirect()->route('admin.users.index')
            ->with('success', __('User updated successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('You cannot delete yourself!'));
        }

        ActivityLogger::log('user.deleted', $user->email, $user, ['name' => $user->name]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', __('User deleted successfully!'));
    }

    /**
     * Toggle admin status for the specified user.
     */
    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('You cannot change your own admin status!'));
        }

        if (! (auth()->user()?->isOwner() ?? false) || $user->isOwner()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('You do not have permission to access this page'));
        }

        $isAdmin = ! $user->is_admin;
        $payload = ['is_admin' => $isAdmin];
        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = $isAdmin ? User::ROLE_ADMIN : null;
        }
        if (Schema::hasColumn('users', 'admin_permissions')) {
            $payload['admin_permissions'] = $isAdmin ? ['dashboard', 'pages'] : null;
        }
        $user->update($payload);

        ActivityLogger::log(
            $user->is_admin ? 'user.promoted_admin' : 'user.demoted_admin',
            $user->name,
            $user,
            ['is_admin' => $user->is_admin]
        );

        return redirect()->route('admin.users.index')
            ->with('success', $user->is_admin
                ? __('User promoted to admin')
                : __('User demoted from admin'));
    }

    /**
     * Toggle ban status for the specified user.
     */
    public function toggleBan(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('You cannot ban yourself!'));
        }

        if ($user->isOwner()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('Owner cannot be banned.'));
        }

        $user->update(['is_banned' => ! $user->is_banned]);

        ActivityLogger::log(
            $user->is_banned ? 'user.banned' : 'user.unbanned',
            $user->name,
            $user,
            ['is_banned' => $user->is_banned]
        );

        return redirect()->route('admin.users.index')
            ->with('success', $user->is_banned
                ? __('User banned successfully!')
                : __('User unbanned successfully!'));
    }

    /**
     * Display the team list.
     */
    public function team()
    {
        $users = User::query()
            ->when(
                Schema::hasColumn('users', 'role'),
                fn ($query) => $query->where(function ($inner) {
                    $inner->whereIn('role', [User::ROLE_OWNER, User::ROLE_ADMIN, User::ROLE_MODERATOR])
                        ->orWhere('is_admin', true);
                }),
                fn ($query) => $query->where('is_admin', true)
            )
            ->where('is_banned', false)
            ->with(['socialAccounts' => function ($query) {
                $query->where('provider', 'discord')
                    ->select('id', 'user_id', 'provider', 'provider_id');
            }])
            ->when(
                Schema::hasColumn('users', 'team_sort_order'),
                fn ($query) => $query->orderByRaw('CASE WHEN team_sort_order IS NULL THEN 1 ELSE 0 END')->orderBy('team_sort_order')
            )
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.team', compact('users'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTeamBanner(Request $request, User $user)
    {
        $request->validate([
            'team_banner_mode' => ['nullable', Rule::in(['color', 'media'])],
            'team_banner_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'team_banner_media_url' => ['nullable', 'url', 'max:255'],
            'team_banner_media' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'team_banner_media_remove' => ['nullable', 'boolean'],
            'team_visible' => ['nullable', 'boolean'],
            'team_sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $mode = (string) $request->input('team_banner_mode', 'color');
        $color = (string) $request->input('team_banner_color', '#7c3aed');
        $mediaUrl = (string) $request->input('team_banner_media_url', '');
        $removeUploaded = $request->boolean('team_banner_media_remove');

        if ($removeUploaded && filled($user->team_banner_media_path)) {
            Storage::disk('public')->delete((string) $user->team_banner_media_path);
            $user->team_banner_media_path = null;
        }

        if ($request->hasFile('team_banner_media')) {
            if (filled($user->team_banner_media_path)) {
                Storage::disk('public')->delete((string) $user->team_banner_media_path);
            }
            $user->team_banner_media_path = $request->file('team_banner_media')->store('team-banners', 'public');
        }

        $user->team_banner_mode = $mode;
        $user->team_banner_color = $color;
        $user->team_banner_media_url = $mediaUrl !== '' ? $mediaUrl : null;
        $user->team_visible = $request->boolean('team_visible');
        $user->team_sort_order = $request->filled('team_sort_order')
            ? (int) $request->input('team_sort_order')
            : null;
        $user->save();

        ActivityLogger::log('user.team_banner.updated', $user->name, $user, [
            'team_banner_mode' => $user->team_banner_mode,
            'team_visible' => $user->team_visible,
            'team_sort_order' => $user->team_sort_order,
        ]);

        return redirect()->route('admin.team')
            ->with('success', __('Team banner updated for :name.', ['name' => $user->name]));
    }
}
