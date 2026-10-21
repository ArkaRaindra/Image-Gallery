<?php

namespace App\Providers;

use App\Models\Tag;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        $forgetCachedPermissions = static function (): void {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        };

        Permission::saved($forgetCachedPermissions);
        Permission::deleted($forgetCachedPermissions);

        View::composer('partials.wiki-sidebar', function ($view): void {
            $view->with('recentWikiPages', Tag::query()
                ->withWiki()
                ->orderByDesc('wiki_updated_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get(['id', 'name', 'category']));
        });
    }
}