<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\AiArticleTopic;
use Illuminate\Http\Request;

class AiArticleTopicController extends Controller
{
    public function index()
    {
        $topics = AiArticleTopic::orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.blog.ai-topics.index', compact('topics'));
    }

    public function create()
    {
        return view('admin.blog.ai-topics.form', ['topic' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'topic'     => 'required|string|max:255',
            'url'       => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        AiArticleTopic::create($data);

        return redirect()->route('blog.ai-topics.index')
            ->with('success', 'Topik auto-blog berhasil ditambahkan.');
    }

    public function edit(AiArticleTopic $aiTopic)
    {
        return view('admin.blog.ai-topics.form', ['topic' => $aiTopic]);
    }

    public function update(Request $request, AiArticleTopic $aiTopic)
    {
        $data = $request->validate([
            'topic'     => 'required|string|max:255',
            'url'       => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $aiTopic->update($data);

        return redirect()->route('blog.ai-topics.index')
            ->with('success', 'Topik auto-blog berhasil diperbarui.');
    }

    public function destroy(AiArticleTopic $aiTopic)
    {
        $aiTopic->delete();

        return redirect()->route('blog.ai-topics.index')
            ->with('success', 'Topik auto-blog berhasil dihapus.');
    }

    /**
     * Seed topik dunia lari default agar scheduler menghasilkan draft tiap hari.
     */
    public function seed()
    {
        $defaults = [
            'Tips latihan lari 10K untuk pemula' => 'https://www.runnersworld.com/',
            'Nutrisi dan pola makan pelari' => 'https://www.runnersworld.com/',
            'Panduan recovery setelah lari jauh' => 'https://www.runnersworld.com/',
            'Rekomendasi sepatu lari terbaik' => 'https://www.runnersworld.com/',
            'Strategi pacing marathon untuk PB' => 'https://www.runnersworld.com/',
            'Cara menghindari cedera lari' => 'https://www.runnersworld.com/',
            'Latihan interval untuk meningkatkan kecepatan' => 'https://www.runnersworld.com/',
        ];

        $added = 0;
        foreach ($defaults as $topic => $url) {
            if (!AiArticleTopic::where('topic', $topic)->exists()) {
                AiArticleTopic::create(['topic' => $topic, 'url' => $url, 'is_active' => true]);
                $added++;
            }
        }

        return redirect()->route('blog.ai-topics.index')
            ->with('success', "Seeded {$added} topik dunia lari default.");
    }
}
