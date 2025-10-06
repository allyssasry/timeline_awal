<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Progress;
use App\Policies\ProjectPolicy;
use App\Policies\ProgressPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class  => ProjectPolicy::class,
        Progress::class => ProgressPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
  public function boot(): void
{
    Gate::define('finalize-project', function ($user, $project) {
        return $user->role === 'digital_banking';
    });
}
}
