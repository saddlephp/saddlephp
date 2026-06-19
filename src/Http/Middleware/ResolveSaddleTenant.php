<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the {tenant} route segment, enforces membership, and binds the
 * tenant on the Saddle singleton for the rest of the request. Runs BEFORE
 * HandleSaddleRequests so the shared Inertia props can reflect the tenant.
 * After binding, the {tenant} route parameter is forgotten so existing
 * controller signatures (which never declared a $tenant argument) keep working.
 */
class ResolveSaddleTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $saddle = app(Saddle::class);

        /** @var class-string<Model> $model */
        $model = $saddle->tenancyModel();
        $param = $request->route('tenant');

        $instance = new $model;

        $tenant = $instance->newQuery()
            ->where($instance->getRouteKeyName(), $param)
            ->first();

        abort_if($tenant === null, 404);

        $relationship = (string) config('saddle.tenancy.relationship', 'users');

        abort_unless(
            $tenant->{$relationship}()->whereKey($request->user()?->getKey())->exists(),
            403,
        );

        $saddle->useTenant($tenant);

        $gate = $saddle->tenantGate();

        if ($gate !== null && ($response = $gate($request, $tenant)) instanceof Response) {
            return $response;
        }

        $request->route()->forgetParameter('tenant');

        return $next($request);
    }
}
