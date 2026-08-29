<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class TenantResolver
{
    /**
     * Resolve tenant from request, authenticated user, domain, or explicit header.
     */
    public function resolve(Request $request): ?Tenant
    {
        // 1. If user is authenticated, resolve directly from user's tenant_id
        $user = $request->user();
        if ($user instanceof User && $user->tenant_id) {
            return Tenant::find($user->tenant_id);
        }

        // 2. Resolve by custom subdomain or host header
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        if (!empty($subdomain) && !in_array($subdomain, ['localhost', '127', 'api', 'www', 'app'])) {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 3. Resolve by X-Tenant-ID header (for API/Flutter clients during pre-auth checks)
        $headerTenant = $request->header('X-Tenant-ID') ?: $request->header('X-Tenant-Subdomain');
        if ($headerTenant) {
            return Tenant::where('id', $headerTenant)
                ->orWhere('subdomain', $headerTenant)
                ->orWhere('code', $headerTenant)
                ->first();
        }

        return null;
    }
}
