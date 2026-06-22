<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportItemsRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\ImportService;
use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller untuk CRUD item, import, dan export.
 * Controller tipis — semua logic ada di ItemService dan ImportService.
 */
class ItemController extends Controller
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly ImportService $importService,
    ) {}

    /** Daftar item terpaginasi dengan filter. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->itemService->list($request->all());

        return ItemResource::collection($items);
    }

    /** Buat item baru. */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image');
        }

        $item = $this->itemService->create($data);

        return (new ItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    /** Update item. */
    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $data = $request->validated();
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image');
        }

        $item = $this->itemService->update($item, $data);

        return new ItemResource($item);
    }

    /** Hapus item (admin only — dijaga di route middleware). */
    public function destroy(Item $item): JsonResponse
    {
        $this->itemService->delete($item);

        return response()->json(['message' => 'Item berhasil dihapus.']);
    }

    /** Import item dari file CSV/Excel. */
    public function import(ImportItemsRequest $request): JsonResponse
    {
        $result = $this->importService->importItems($request->file('file'));

        return response()->json([
            'message' => "Import selesai. Berhasil: {$result['success']}, Gagal: {$result['failed']}.",
            'data' => $result,
        ]);
    }

    /** Export item ke PDF atau Excel. */
    public function export(Request $request, string $format): mixed
    {
        $exporter = app(\App\Exports\ItemsExport::class);

        return $exporter->export($format);
    }
}
