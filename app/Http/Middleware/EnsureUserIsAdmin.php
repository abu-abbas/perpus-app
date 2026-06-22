<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses hanya untuk user dengan role admin.
 * Dipakai di route yang melibatkan operasi destruktif (hapus data).
 */
class EnsureUserIsAdmin
{
    /**
     * Periksa apakah user yang sedang login adalah admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melakukan aksi ini.',
            ], 403);
        }

        return $next($request);
    }
}
