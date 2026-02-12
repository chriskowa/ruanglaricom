<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    public function index()
    {
        $communities = Community::with('city')->latest()->paginate(10);

        return view('admin.communities.index', compact('communities'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->get();

        return view('admin.communities.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:communities,slug',
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
            'pic_phone' => 'required|string|max:20',
            'city_id' => 'nullable|exists:cities,id',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'hero_image' => 'nullable|image|max:2048',
            'theme_color' => 'nullable|string',
            'wa_group_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'tiktok_link' => 'nullable|url',
            'schedules' => 'nullable|array',
            'schedules.*.day' => 'required_with:schedules|string',
            'schedules.*.time' => 'nullable|string',
            'schedules.*.activity' => 'nullable|string',
            'schedules.*.location' => 'nullable|string',
            'captains' => 'nullable|array',
            'captains.*.name' => 'required_with:captains|string',
            'captains.*.role' => 'nullable|string',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required_with:faqs|string',
            'faqs.*.answer' => 'required_with:faqs|string',
        ]);

        $data = $validated;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('communities/logos', 'public');
        }

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('communities/heroes', 'public');
        }

        // Handle Captains Images
        if (isset($data['captains']) && is_array($data['captains'])) {
            $processedCaptains = [];
            foreach ($data['captains'] as $cap) {
                $imagePath = null;
                if (isset($cap['image']) && $cap['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imagePath = $cap['image']->store('communities/captains', 'public');
                }
                $processedCaptains[] = [
                    'name' => $cap['name'] ?? '',
                    'role' => $cap['role'] ?? '',
                    'image' => $imagePath,
                ];
            }
            $data['captains'] = $processedCaptains;
        }

        Community::create($data);

        return redirect()->route('admin.communities.index')
            ->with('success', 'Community created successfully.');
    }

    public function show(Community $community)
    {
        $community->load(['city', 'members']);

        return view('admin.communities.show', compact('community'));
    }

    public function edit(Community $community)
    {
        $cities = City::orderBy('name')->get();

        return view('admin.communities.edit', compact('community', 'cities'));
    }

    public function update(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:communities,slug,'.$community->id,
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
            'pic_phone' => 'required|string|max:20',
            'city_id' => 'nullable|exists:cities,id',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'hero_image' => 'nullable|image|max:2048',
            'theme_color' => 'nullable|string',
            'wa_group_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'tiktok_link' => 'nullable|url',
            'schedules' => 'nullable|array',
            'schedules.*.day' => 'required_with:schedules|string',
            'schedules.*.time' => 'nullable|string',
            'schedules.*.activity' => 'nullable|string',
            'schedules.*.location' => 'nullable|string',
            'captains' => 'nullable|array',
            'captains.*.name' => 'required_with:captains|string',
            'captains.*.role' => 'nullable|string',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required_with:faqs|string',
            'faqs.*.answer' => 'required_with:faqs|string',
        ]);

        $data = $validated;

        if ($request->hasFile('logo')) {
            if ($community->logo) {
                Storage::disk('public')->delete($community->logo);
            }
            $data['logo'] = $request->file('logo')->store('communities/logos', 'public');
        } elseif ($request->input('remove_logo') == '1') {
            if ($community->logo) {
                Storage::disk('public')->delete($community->logo);
            }
            $data['logo'] = null;
        }

        if ($request->hasFile('hero_image')) {
            if ($community->hero_image) {
                Storage::disk('public')->delete($community->hero_image);
            }
            $data['hero_image'] = $request->file('hero_image')->store('communities/heroes', 'public');
        } elseif ($request->input('remove_hero_image') == '1') {
            if ($community->hero_image) {
                Storage::disk('public')->delete($community->hero_image);
            }
            $data['hero_image'] = null;
        }

        // Handle Captains
        if ($request->has('captains')) {
            $processedCaptains = [];
            foreach ($request->captains as $index => $cap) {
                $imagePath = $cap['existing_image'] ?? null;

                if (isset($cap['image']) && $cap['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imagePath = $cap['image']->store('communities/captains', 'public');
                } elseif (isset($cap['remove_image']) && $cap['remove_image'] == '1') {
                    $imagePath = null;
                }

                $processedCaptains[] = [
                    'name' => $cap['name'] ?? '',
                    'role' => $cap['role'] ?? '',
                    'image' => $imagePath,
                ];
            }
            $data['captains'] = $processedCaptains;
        } else {
            $data['captains'] = [];
        }

        if (! $request->has('schedules')) {
            $data['schedules'] = [];
        }

        if (! $request->has('faqs')) {
            $data['faqs'] = [];
        }

        $community->update($data);

        return redirect()->route('admin.communities.index')
            ->with('success', 'Community updated successfully.');
    }

    public function destroy(Community $community)
    {
        $community->delete();

        return redirect()->route('admin.communities.index')
            ->with('success', 'Community deleted successfully.');
    }
}
