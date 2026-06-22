<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller untuk CRUD anggota perpustakaan.
 */
class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    /** Daftar anggota terpaginasi. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $members = $this->memberService->list($request->all());

        return MemberResource::collection($members);
    }

    /** Buat anggota baru. */
    public function store(StoreMemberRequest $request): MemberResource
    {
        $member = $this->memberService->create($request->validated());

        return new MemberResource($member);
    }

    /** Update anggota. */
    public function update(UpdateMemberRequest $request, Member $member): MemberResource
    {
        $member = $this->memberService->update($member, $request->validated());

        return new MemberResource($member);
    }

    /** Hapus anggota (admin only). */
    public function destroy(Member $member): JsonResponse
    {
        $this->memberService->delete($member);

        return response()->json(['message' => 'Anggota berhasil dihapus.']);
    }
}
