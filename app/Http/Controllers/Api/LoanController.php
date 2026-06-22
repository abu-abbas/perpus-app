<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Item;
use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller untuk peminjaman dan pengembalian item.
 * Controller tipis — BusinessException akan di-render otomatis oleh Laravel.
 */
class LoanController extends Controller
{
    public function __construct(
        private readonly LoanService $loanService,
    ) {}

    /** Daftar peminjaman terpaginasi. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $loans = $this->loanService->list($request->all());

        return LoanResource::collection($loans);
    }

    /** Buat peminjaman baru. */
    public function store(StoreLoanRequest $request): LoanResource
    {
        // Service akan lempar BusinessException jika aturan dilanggar
        // Exception otomatis ter-render jadi response JSON oleh Laravel
        $loan = $this->loanService->borrow(
            member: Member::findOrFail($request->integer('member_id')),
            item: Item::findOrFail($request->integer('item_id')),
            librarian: $request->user(),
        );

        return new LoanResource($loan);
    }

    /** Proses pengembalian item. */
    public function returnLoan(Loan $loan): LoanResource
    {
        $loan->load(['item', 'member']);

        $loan = $this->loanService->return($loan);

        return new LoanResource($loan);
    }
}
