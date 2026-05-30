<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static ?bool $hasRoleColumn = null;
    protected static ?bool $hasAdminPermissionsColumn = null;

    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';

    /**
     * @return array<string, string>
     */
    public static function adminPermissionOptions(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'news' => 'News',
            'pages' => 'Pages',
            'forms' => 'Forms',
            'users' => 'Users',
            'team' => 'Team',
            'settings' => 'General Settings',
            'operations' => 'Operations',
            'activity_log' => 'Activity log',
            'login_history' => 'Login history',
            'system_update' => 'System updates',
            'server_info' => 'Server information',
            'health_check' => 'Health check',
            'security' => 'Two-factor authentication',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'team_banner_mode',
        'team_banner_color',
        'team_banner_media_url',
        'team_banner_media_path',
        'team_visible',
        'team_sort_order',
        'bio',
        'task',
        'is_admin',
        'role',
        'admin_permissions',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'admin_permissions' => 'array',
            'is_banned' => 'boolean',
            'team_visible' => 'boolean',
            'team_sort_order' => 'integer',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'admin_notif_last_seen_at' => 'datetime',
            'admin_notif_feed_floor_at' => 'datetime',
        ];
    }

    /**
     * Get the news articles for the user.
     */
    public function news()
    {
        return $this->hasMany(News::class);
    }

    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        if (! $this->supportsRolePermissions()) {
            return (bool) $this->is_admin;
        }

        return $this->isOwner()
            || in_array((string) $this->role, [self::ROLE_ADMIN, self::ROLE_MODERATOR], true)
            || (bool) $this->is_admin;
    }

    public function isOwner(): bool
    {
        if (! $this->supportsRolePermissions()) {
            return false;
        }

        return (string) $this->role === self::ROLE_OWNER;
    }

    public function isModerator(): bool
    {
        if (! $this->supportsRolePermissions()) {
            return false;
        }

        return (string) $this->role === self::ROLE_MODERATOR;
    }

    public function adminRoleLabel(): string
    {
        if (! $this->supportsRolePermissions()) {
            return ((bool) $this->is_admin ? 'Admin' : 'User');
        }

        return match ((string) $this->role) {
            self::ROLE_OWNER => 'Owner',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            default => ((bool) $this->is_admin ? 'Admin' : 'User'),
        };
    }

    public function hasAdminPermission(string $permission): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        if (! $this->supportsRolePermissions()) {
            return true;
        }

        if ($this->isOwner()) {
            return true;
        }

        if (! $this->supportsAdminPermissionsColumn()) {
            return true;
        }

        // Legacy fallback: old admins (is_admin=1) without role/permissions must not be locked out.
        if ((string) $this->role === '' && (bool) $this->is_admin) {
            return true;
        }

        $permissions = $this->normalizedAdminPermissions();
        if ($permissions === null) {
            return false;
        }

        if ((string) $this->role === self::ROLE_MODERATOR && $permissions === []) {
            return $permission === 'dashboard';
        }

        if (in_array($permission, ['pages', 'forms'], true) && in_array('dashboard', $permissions, true)) {
            return true;
        }

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    /**
     * @return array<int, string>|null
     */
    public function normalizedAdminPermissions(): ?array
    {
        $permissions = $this->admin_permissions;

        if (is_string($permissions) && $permissions !== '') {
            $decoded = json_decode($permissions, true);
            if (is_array($decoded)) {
                $permissions = $decoded;
            }
        }

        if (! is_array($permissions)) {
            return null;
        }

        $permissions = array_values(array_filter(array_map(
            static fn ($value) => is_string($value) ? trim($value) : '',
            $permissions
        )));

        return array_values(array_unique($permissions));
    }

    protected function supportsRolePermissions(): bool
    {
        if (self::$hasRoleColumn === null) {
            self::$hasRoleColumn = Schema::hasColumn($this->getTable(), 'role');
        }

        return self::$hasRoleColumn;
    }

    protected function supportsAdminPermissionsColumn(): bool
    {
        if (self::$hasAdminPermissionsColumn === null) {
            self::$hasAdminPermissionsColumn = Schema::hasColumn($this->getTable(), 'admin_permissions');
        }

        return self::$hasAdminPermissionsColumn;
    }

    /**
     * Check if the user is banned.
     */
    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    /**
     * Admins with aktiviertem TOTP.
     */
    public function hasTwoFactorEnabled(): bool
    {
        $secret = $this->getTwoFactorSecretSafely();

        return $this->is_admin
            && $this->two_factor_confirmed_at !== null
            && filled($secret);
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        $secret = $this->getTwoFactorSecretSafely();
        if (! $secret) {
            return false;
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA;

        return $google2fa->verifyKey($secret, preg_replace('/\s+/', '', $code), 4);
    }

    protected function getTwoFactorSecretSafely(): ?string
    {
        try {
            $secret = $this->two_factor_secret;
        } catch (DecryptException) {
            return null;
        } catch (\Throwable) {
            return null;
        }

        $secret = is_string($secret) ? trim($secret) : '';

        return $secret !== '' ? $secret : null;
    }
}
