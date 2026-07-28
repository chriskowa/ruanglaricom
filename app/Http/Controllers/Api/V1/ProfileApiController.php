<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileApiController extends BaseApiController
{
    /**
     * Get authenticated runner profile
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse(new UserResource($user), 'Profil pelari berhasil dimuat');
    }

    /**
     * Update runner personal profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|nullable|string|in:male,female,Pria,Wanita',
            'phone' => 'sometimes|nullable|string|max:30',
            'city_id' => 'sometimes|nullable|integer|exists:cities,id',
            'weight' => 'sometimes|nullable|numeric|min:20|max:300',
            'height' => 'sometimes|nullable|numeric|min:50|max:250',
            'weekly_volume' => 'sometimes|nullable|numeric|min:0|max:500',
            'weekly_km_target' => 'sometimes|nullable|numeric|min:0|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi profil gagal', 422, $validator->errors());
        }

        $updates = [];
        if ($request->has('name')) {
            $updates['name'] = trim($request->name);
        }
        if ($request->has('gender')) {
            $updates['gender'] = in_array(strtolower((string) $request->gender), ['female', 'wanita'], true) ? 'female' : 'male';
        }
        if ($request->has('phone')) {
            $updates['phone'] = $request->phone;
        }
        if ($request->has('city_id')) {
            $updates['city_id'] = $request->city_id;
        }
        if ($request->has('weight')) {
            $updates['weight'] = $request->weight;
        }
        if ($request->has('height')) {
            $updates['height'] = $request->height;
        }
        if ($request->has('weekly_volume')) {
            $updates['weekly_volume'] = $request->weekly_volume;
        }
        if ($request->has('weekly_km_target')) {
            $updates['weekly_km_target'] = $request->weekly_km_target;
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        return $this->successResponse(new UserResource($user->fresh()), 'Profil pelari berhasil diperbarui');
    }

    /**
     * Update runner personal bests & training target
     */
    public function updatePaces(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'pb_5k' => 'nullable|string|max:20',
            'pb_10k' => 'nullable|string|max:20',
            'pb_hm' => 'nullable|string|max:20',
            'pb_fm' => 'nullable|string|max:20',
            'pb_balke' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi PB lari gagal', 422, $validator->errors());
        }

        $user->update([
            'pb_5k' => $request->pb_5k ?: $user->pb_5k,
            'pb_10k' => $request->pb_10k ?: $user->pb_10k,
            'pb_hm' => $request->pb_hm ?: $user->pb_hm,
            'pb_fm' => $request->pb_fm ?: $user->pb_fm,
            'pb_balke' => $request->pb_balke ?: $user->pb_balke,
        ]);

        return $this->successResponse(new UserResource($user->fresh()), 'Personal Best (PB) berhasil diperbarui');
    }
}
