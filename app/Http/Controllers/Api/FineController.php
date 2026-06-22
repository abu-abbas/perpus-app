<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Services\FineService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller untuk daftar denda dan pembayaran.
 */
class FineController extends Controller
{
    public function __construct(
        private readonly FineService $fineService,
    ) {}

    /** Daftar denda terpaginasi. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $fines = $this->fineService->listFines($request->all());

        return FineResource::collection($fines);
    }

    /** Tandai denda sebagai lunas. */
    public function pay(Fine $fine): FineResource
    {
        $fine = $this->fineService->markPaid($fine);

        return new FineResource($fine->load('loan'));
    }
}
