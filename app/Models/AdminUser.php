<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLES = [self::ROLE_ADMIN => 'Administrator', self::ROLE_EDITOR => 'Editor'];

    /**
     * Admin areas an Editor may use (route name segment after "admin."):
     * Packages, Pages and Posts (travel guides), Media, and the Categories and Tags
     * (tour types and destinations). Everything else is administrators only.
     */
    public const EDITOR_AREAS = ['packages', 'posts', 'pages', 'media', 'tour-types', 'destinations'];

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'last_login_at' => 'datetime'];
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isAdministrator(): bool
    {
        return ! $this->isEditor();
    }

    /** Can this user open the given admin route (by route name)? */
    public function canAccessRoute(?string $routeName): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }
        if (in_array($routeName, ['admin.dashboard', 'admin.logout'], true)) {
            return true;
        }
        $parts = explode('.', (string) $routeName);

        return ($parts[0] ?? '') === 'admin' && in_array($parts[1] ?? '', self::EDITOR_AREAS, true);
    }
}
