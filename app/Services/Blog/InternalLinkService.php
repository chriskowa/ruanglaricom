<?php

namespace App\Services\Blog;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\Event;
use Illuminate\Support\Str;

class InternalLinkService
{
    /**
     * Cari daftar target internal link yang relevan berdasarkan topik, kata kunci, dan kategori.
     *
     * @param string      $topic
     * @param string      $keyword
     * @param string|null $categorySlug
     * @param int         $limit
     * @param int|null    $excludeArticleId
     * @return array<int, array{title: string, url: string, keywords: array<string>, type: string}>
     */
    public function getRelevantTargets(
        string $topic,
        string $keyword = '',
        ?string $categorySlug = null,
        int $limit = 8,
        ?int $excludeArticleId = null
    ): array {
        $targets = [];
        $searchTerms = $this->extractSearchTerms($topic, $keyword);

        // 1. Core Pillar / Tools Pages (selalu relevan untuk topik lari terkait)
        $pillarPages = $this->getPillarPages($topic . ' ' . $keyword);
        foreach ($pillarPages as $p) {
            $targets[] = $p;
        }

        // 2. Kategori Terkait
        $categories = BlogCategory::query()
            ->when($categorySlug, function ($q) use ($categorySlug) {
                $q->where('slug', '!=', $categorySlug);
            })
            ->get(['id', 'name', 'slug']);

        foreach ($categories as $cat) {
            $catName = strtolower($cat->name);
            $isMatch = false;
            foreach ($searchTerms as $term) {
                if (strlen($term) >= 3 && (str_contains($catName, $term) || str_contains($term, $catName))) {
                    $isMatch = true;
                    break;
                }
            }
            if ($isMatch) {
                $targets[] = [
                    'title'    => 'Kategori ' . $cat->name,
                    'url'      => url('/blog/kategori/' . $cat->slug),
                    'keywords' => [strtolower($cat->name), 'artikel ' . strtolower($cat->name)],
                    'type'     => 'category',
                ];
            }
        }

        // 3. Artikel Terbitan Terpopuler / Terkait
        $articleQuery = Article::published()
            ->when($excludeArticleId, function ($q) use ($excludeArticleId) {
                $q->where('id', '!=', $excludeArticleId);
            });

        if (!empty($searchTerms)) {
            $articleQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (strlen($term) >= 3) {
                        $q->orWhere('title', 'like', "%{$term}%")
                          ->orWhere('focus_keyword', 'like', "%{$term}%")
                          ->orWhere('meta_keywords', 'like', "%{$term}%");
                    }
                }
            });
        }

        $matchedArticles = $articleQuery->orderByDesc('views_count')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'focus_keyword', 'meta_keywords']);

        // Jika pencarian spesifik sedikit, lengkapi dengan artikel terpopuler terbaru
        if ($matchedArticles->count() < 4) {
            $existingIds = $matchedArticles->pluck('id')->toArray();
            if ($excludeArticleId) {
                $existingIds[] = $excludeArticleId;
            }

            $fallbackArticles = Article::published()
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('views_count')
                ->limit(6 - $matchedArticles->count())
                ->get(['id', 'title', 'slug', 'focus_keyword', 'meta_keywords']);

            $matchedArticles = $matchedArticles->merge($fallbackArticles);
        }

        foreach ($matchedArticles as $art) {
            $kws = [];
            if (!empty($art->focus_keyword)) {
                $kws[] = strtolower(trim($art->focus_keyword));
            }
            if (!empty($art->meta_keywords)) {
                $rawMeta = explode(',', $art->meta_keywords);
                foreach ($rawMeta as $m) {
                    $mClean = strtolower(trim($m));
                    if ($mClean !== '' && strlen($mClean) >= 3 && !in_array($mClean, $kws, true)) {
                        $kws[] = $mClean;
                    }
                }
            }
            // Tambahkan judul bersih sebagai variasi anchor
            $cleanTitle = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $art->title)));
            if ($cleanTitle !== '' && !in_array($cleanTitle, $kws, true)) {
                $kws[] = $cleanTitle;
            }

            $targets[] = [
                'title'    => $art->title,
                'url'      => url('/blog/' . $art->slug),
                'keywords' => array_slice($kws, 0, 3),
                'type'     => 'article',
            ];
        }

        // 4. Event Lari Terkini (jika relevan dengan jadwal / event)
        if (preg_match('/event|jadwal|marathon|race|lari|lomba/i', $topic . ' ' . $keyword)) {
            $events = Event::where('status', 'published')
                ->where('is_active', true)
                ->where('start_at', '>=', now()->subDays(7))
                ->orderBy('start_at')
                ->limit(3)
                ->get(['id', 'name', 'slug', 'location_name']);

            foreach ($events as $ev) {
                $targets[] = [
                    'title'    => 'Event ' . $ev->name,
                    'url'      => url('/events/' . $ev->slug),
                    'keywords' => [strtolower($ev->name), 'event ' . strtolower($ev->name)],
                    'type'     => 'event',
                ];
            }
        }

        return array_slice($targets, 0, $limit);
    }

    /**
     * Format target internal link ke dalam teks instruksi prompt AI.
     */
    public function formatPromptInstruction(array $targets): string
    {
        if (empty($targets)) {
            return '';
        }

        $lines = [];
        foreach ($targets as $t) {
            $kwStr = !empty($t['keywords']) ? implode(', ', $t['keywords']) : $t['title'];
            $lines[] = "- {$t['url']} (Contoh anchor relevan: {$kwStr})";
        }

        return "INSTRUKSI PENYISIPAN INTERNAL LINK OTOMATIS (WAJIB SISIPKAN 2-4 LINK INTERNAL):\n" .
               "Sisipkan hyperlink <a href=\"URL\">anchor text</a> secara alami dan kontekstual ke dalam paragraf isi artikel (HANYA di dalam tag <p>, JANGAN di dalam <h2>/<h3>/<blockquote>/tabel).\n" .
               "Pilih 2 hingga 4 tautan yang paling relevan dari daftar internal link Ruang Lari berikut:\n" .
               implode("\n", $lines) . "\n" .
               "- Ketentuan: Anchor text harus mengalir alami, relevan dengan konteks kalimat, dan dilarang menggunakan kata 'klik di sini'. Jangan menumpuk lebih dari 1 link dalam kalimat yang sama.";
    }

    /**
     * Post-processing: Otomatis memindai HTML artikel dan menambahkan internal link
     * jika belum ada atau masih kurang, secara aman tanpa merusak tag HTML yang sudah ada.
     */
    public function injectInternalLinks(string $html, array $targets, int $maxLinks = 3): string
    {
        if (empty($html) || empty($targets)) {
            return $html;
        }

        // Cek berapa banyak link internal yang sudah ada di HTML
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $html, $existingMatches);
        $usedUrls = [];
        if (!empty($existingMatches[1])) {
            foreach ($existingMatches[1] as $url) {
                $cleanUrl = strtolower(rtrim($url, '/'));
                $usedUrls[$cleanUrl] = true;
            }
        }

        $linksAdded = count($usedUrls);
        if ($linksAdded >= $maxLinks) {
            return $html;
        }

        // Pisahkan teks per paragraf <p>...</p> agar hanya memodifikasi konten di dalam <p>
        $pattern = '/(<p\b[^>]*>)(.*?)(<\/p>)/is';

        $html = preg_replace_callback($pattern, function ($matches) use (&$targets, &$usedUrls, &$linksAdded, $maxLinks) {
            $openingTag = $matches[1];
            $content    = $matches[2];
            $closingTag = $matches[3];

            // Jika paragraf ini mengandung [Gambar: ...] atau sudah punya link, lewati untuk mencegah tumpang tindih
            if (str_contains($content, '[Gambar:') || str_contains($content, '<a ')) {
                return $matches[0];
            }

            if ($linksAdded >= $maxLinks) {
                return $matches[0];
            }

            foreach ($targets as $target) {
                if ($linksAdded >= $maxLinks) {
                    break;
                }

                $targetUrl = $target['url'];
                $cleanTargetUrl = strtolower(rtrim($targetUrl, '/'));
                if (isset($usedUrls[$cleanTargetUrl])) {
                    continue;
                }

                $keywords = $target['keywords'] ?? [$target['title']];
                foreach ($keywords as $kw) {
                    $kw = trim($kw);
                    if (strlen($kw) < 4) {
                        continue;
                    }

                    // Cari kata kunci dengan boundary, case-insensitive, tapi hindari dalam tag
                    $regex = '/(?<!<[^>]*)\b(' . preg_quote($kw, '/') . ')\b(?![^<]*>)/iu';
                    if (preg_match($regex, $content, $kwMatch, PREG_OFFSET_CAPTURE)) {
                        $matchedText = $kwMatch[1][0];
                        $replacement = '<a href="' . htmlspecialchars($targetUrl) . '">' . $matchedText . '</a>';
                        
                        // Replace hanya kemunculan pertama pada paragraf ini
                        $content = preg_replace($regex, $replacement, $content, 1);
                        $usedUrls[$cleanTargetUrl] = true;
                        $linksAdded++;
                        break;
                    }
                }
            }

            return $openingTag . $content . $closingTag;
        }, $html);

        return $html;
    }

    /**
     * Daftar halaman pilar / tools inti Ruang Lari.
     */
    private function getPillarPages(string $context): array
    {
        $pillars = [];
        $contextLower = strtolower($context);

        // Jadwal Lari
        $pillars[] = [
            'title'    => 'Jadwal Lari 2026 Indonesia',
            'url'      => url('/jadwal-lari'),
            'keywords' => ['jadwal lari 2026', 'kalender lari', 'event lari 2026', 'jadwal marathon'],
            'type'     => 'pillar',
        ];

        // Kalkulator Lari & Pace
        if (preg_match('/pace|kecepatan|waktu|target|jarak|kalkulator|finish|hitung/i', $contextLower)) {
            $pillars[] = [
                'title'    => 'Kalkulator Pace & Waktu Lari',
                'url'      => url('/tools/calculator'),
                'keywords' => ['kalkulator pace lari', 'kalkulator lari', 'hitung pace lari', 'prediksi waktu lari'],
                'type'     => 'tool',
            ];
        }

        // Buat Rute Lari GPX
        if (preg_match('/rute|gpx|elevasi|track|jalur|strava|garmin|trail/i', $contextLower)) {
            $pillars[] = [
                'title'    => 'Buat & Analisis Rute Lari GPX',
                'url'      => url('/tools/buat-rute-lari'),
                'keywords' => ['buat rute lari', 'rute lari gpx', 'download rute lari gpx'],
                'type'     => 'tool',
            ];
        }

        return $pillars;
    }

    /**
     * Ekstrak token kata penting untuk pencarian relevansi.
     */
    private function extractSearchTerms(string $topic, string $keyword): array
    {
        $combined = strtolower($topic . ' ' . $keyword);
        // Hapus karakter non-alfanumerik
        $cleaned = preg_replace('/[^a-z0-9\s]/', ' ', $combined);
        $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        $stopWords = [
            'dan', 'atau', 'yang', 'untuk', 'pada', 'dengan', 'dari', 'ke', 'di', 'ini', 'itu',
            'adalah', 'sebagai', 'dalam', 'bisa', 'cara', 'tips', 'agar', 'saat', 'lebih', 'bagaimana',
            'the', 'and', 'for', 'with', 'from', 'in', 'on', 'at', 'to', 'how', 'what', 'why'
        ];

        $terms = [];
        foreach ($words as $w) {
            if (strlen($w) >= 3 && !in_array($w, $stopWords, true) && !in_array($w, $terms, true)) {
                $terms[] = $w;
            }
        }

        if (!empty($keyword) && strlen($keyword) >= 3 && !in_array(strtolower($keyword), $terms, true)) {
            array_unshift($terms, strtolower(trim($keyword)));
        }

        return $terms;
    }
}
