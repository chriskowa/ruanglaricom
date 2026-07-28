<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Community;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityApiController extends BaseApiController
{
    /**
     * Browse running communities in Indonesia
     */
    public function index(Request $request): JsonResponse
    {
        $query = Community::query()
            ->withCount('members');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('city_name', 'like', "%{$search}%");
        }

        if ($request->filled('city')) {
            $city = trim($request->city);
            $query->where('city_name', 'like', "%{$city}%");
        }

        $communities = $query->orderBy('name', 'asc')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse([
            'communities' => $communities->items(),
            'pagination' => [
                'total' => $communities->total(),
                'per_page' => $communities->perPage(),
                'current_page' => $communities->currentPage(),
                'last_page' => $communities->lastPage(),
            ],
        ], 'Daftar komunitas lari berhasil dimuat');
    }

    /**
     * Get detail of a running community
     */
    public function show(string $slug): JsonResponse
    {
        $community = Community::where('slug', $slug)
            ->orWhere('id', $slug)
            ->withCount('members')
            ->with(['members' => fn ($q) => $q->take(10)])
            ->first();

        if (! $community) {
            return $this->errorResponse('Komunitas lari tidak ditemukan.', 404);
        }

        return $this->successResponse($community, 'Detail komunitas lari berhasil dimuat');
    }
}
