<?php

namespace App\Services\Admin;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Ramsey\Uuid\Uuid;
use App\Helpers\General;
use App\Models\ArticleAgent;
use App\Lib\GeminiClient;
use App\Lib\TavilyClient;
use App\Lib\OpenAIClient;

class AdminArticleAgentService
{
  private $helper;
  private $gemini;
  private $tavily;
  private $openai;

  private $modelGeminiThink      = 'gemini-2.5-pro';
  private $modelGeminiSummary    = 'gemini-2.5-flash-lite';
  private $modelOpenaiBrainstorm = 'gpt-5.5';
  private $modelOpenaiSummary    = 'gpt-5.4-mini';
  private $modelOpenaiWriting    = 'gpt-5.5';


  public function __construct()
  {
    $this->helper = new General;
    $geminiKey = $_ENV['GEMINI_API_KEY'] ?? null;
    $tavilyKey = $_ENV['TAVILY_API_KEY'] ?? null;
    $openaiKey = $_ENV['OPENAI_API_KEY'] ?? null;

    if (empty($geminiKey) || empty($tavilyKey) || empty($openaiKey)) {
      throw new Exception("Missing API Keys. Please configure GEMINI_API_KEY, TAVILY_API_KEY, and OPENAI_API_KEY in .env");
    }

    $this->gemini = new GeminiClient($geminiKey);
    $this->tavily = new TavilyClient($tavilyKey);
    $this->openai = new OpenAIClient($openaiKey);
  }

  /**
   * Langkah 3 Skenario A: Input Topik (AI Brainstorming)
   */
  public function step1_inputTopic(array $input)
  {
    $topic    = $input['topic'];
    $strategy = $input['strategy'] ?? 'free';

    //? Get Top Articles as Reference
    $topArticles = [];
    if ($strategy !== 'free') {
      $site        = $input['site'] ?? 'all';
      $topArticles = $this->helper->getTopArticles($site, 50);
    }

    //* 1. Susun Base Prompt
    $prompt = "Kamu adalah seorang ahli strategi konten SEO. Dengan topik utama '{$topic}', buatlah 10 ide artikel unik dan berpotensi trafik tinggi.\n\n";

    //* 2. Inject Referensi & Strategi
    if ($strategy !== 'free' && !empty($topArticles)) {
      $prompt .= "REFERENSI 50 ARTIKEL TERPOPULER SAYA:\n";
      foreach ($topArticles as $index => $article) {
        $num = $index + 1;
        $prompt .= "{$num}. {$article['title']} | Keyword: {$article['keyword']}\n";
      }
      $prompt .= "\nINSTRUKSI STRATEGI:\n";

      switch ($strategy) {
        case 'gap': //! "Hindari Topik Serupa (Cari Celah Baru)"
          $prompt .= "Bandingkan topik '{$topic}' dengan daftar referensi di atas. Buat 10 ide artikel dengan angle yang SAMA SEKALI BERBEDA dan belum ter-cover di daftar tersebut untuk mencegah keyword cannibalization.\n\n";
          break;
        case 'cluster': //! "Buat Topik Turunan (Pillar & Cluster)"
          $prompt .= "Gunakan daftar referensi di atas sebagai 'Pillar'. Buat 10 ide artikel turunan (cluster content) dari topik '{$topic}' yang bisa mendalami referensi tersebut dan sangat relevan untuk dipasang internal link menuju daftar referensi.\n\n";
          break;
        case 'formula': //! "Tiru Formula Judul Teratas"
          $prompt .= "Analisis pola psikologis (misal: listicle, FOMO, how-to) dari daftar referensi di atas. Terapkan formula penulisan judul dan angle sukses tersebut ke 10 ide artikel baru untuk topik '{$topic}'.\n\n";
          break;
      }
    }

    //* 3. Format Output JSON Strict
    $prompt .= "Untuk setiap ide, berikan:\n" .
               "1. Judul yang memancing klik (CTR tinggi)\n" .
               "2. Kata kunci utama (Target Keyword)\n" .
               "3. Ringkasan singkat isi konten (Maksimal 2 kalimat).\n\n" .
               "KEMBALIKAN HASILNYA HANYA SEBAGAI ARRAY JSON objek dengan kunci persis seperti ini: 'title', 'keyword', 'summary'. Jangan sertakan format markdown, backticks (```json), atau teks pengantar apa pun di luar JSON.";

    //* 4. Hit LLM (OpenAI / Gemini)
    $rawResponse = $this->openai->generateContent("", $prompt, $this->modelOpenaiBrainstorm, [
      // 'temperature' => 0.7,
    ]);

    if (!$rawResponse) {
      throw new Exception("Failed to generate brainstorming options from Models.");
    }

    //* 5. Bersihkan dan Decode JSON
    $cleanJson    = trim(str_replace(['```json', '```'], '', $rawResponse));
    $optionsArray = json_decode($cleanJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new Exception("Output bukan JSON valid: " . json_last_error_msg() . " | Raw: " . substr($rawResponse, 0, 100));
    }

    //* 6. Generate UUID & Simpan DB
    $uuid = Uuid::uuid4()->toString();
    ArticleAgent::create([
      'id'                    => $uuid,
      'user_input_topic'      => $topic,
      'brainstorming_options' => $optionsArray,
    ]);

    //* 7. Return Hasil
    return [
      'uuid'    => $uuid,
      'options' => $optionsArray
    ];
  }

  /**
   * Langkah 3 Skenario A (Select Option) & Skenario B (Manual Input)
   */
  public function step1_inputSelection(array $input)
  {
    // $input expected: 
    // - uuid (optional)
    // - selection (array: {title, keyword, intent}) - if picking from brainstorming
    // - manual_input (boolean) - flag if this is direct manual input

    $uuid          = $input['uuid'] ?? null;
    $selectionData = $input['selection'] ?? null;
    if (!$selectionData) {
      throw new Exception("Selection data is required.");
    }

    if ($uuid) {
      // --- KONDISI: Session Sudah Ada ---
      $session = ArticleAgent::find($uuid);
      if (!$session) {
        throw new Exception("Session not found.");
      }

      $session->update([
        'selected_option_data' => $selectionData
      ]);
    } 
    else {
      // --- KONDISI: Session Baru ---
      $uuid = Uuid::uuid4()->toString();
      ArticleAgent::create([
        'id'                   => $uuid,
        'selected_option_data' => $selectionData,
      ]);
    }

    $researchManual = $input['research_manual'] ?? false;
    $researchResult = $this->step2_doResearch(['uuid' => $uuid, 'research_manual' => $researchManual]);
    return array_merge(['uuid' => $uuid], $researchResult);
  }

  /**
   * Langkah 4: Research (Deep Dive)
   */
  public function step2_doResearch(array $input)
  {
    $session = ArticleAgent::find($input['uuid']);
    if (!$session) {
      throw new Exception("Session not found.");
    }

    $selectedData = $session->selected_option_data;
    if (!$selectedData || !isset($selectedData['keyword'])) {
      throw new Exception("No keyword selected for research.");
    }

    //* JIKA MANUAL, SKIP TAVILY & OPENAI
    $researchManual = $input['research_manual'] ?? false;
    if ($researchManual) {
      return [
        'uuid'                => $input['uuid'],
        'research_raw_tavily' => null,
        'research_summary'    => null,
        'cleaned'             => null
      ];
    }

    //* 1. Tavily Search
    $query        = ($selectedData['title'] ?? "") . ". Keyword: " . $selectedData['keyword'];
    $tavilyResult = $this->tavily->search($query, 7, ['youtube.com', 'tiktok.com']);
    if (!$tavilyResult) {
      throw new Exception("Failed to retrieve research data.");
    }

    //* 2. Extract text for Research
    $textForResearch = $this->helper->clean_tavily_context($tavilyResult['results'] ?? [], 12000);

    //! Prompt
    $prompt = "Kamu adalah seorang analis riset profesional. Analisis data riset mentah berikut yang berisi cuplikan dan konten dari hasil pencarian.\n" .
              "Tugas:\n" .
              "1. Saring informasi yang tidak relevan atau duplikat.\n" .
              "2. Sintesiskan poin-poin penting menjadi ringkasan yang komprehensif.\n" .
              "3. Sertakan fakta, statistik, dan wawasan yang relevan.\n" .
              "4. Cantumkan URL sumber untuk klaim utama jika memungkinkan.\n" .
              "\nData Mentah:\n" . $textForResearch . "\n\n" .
              "INSTRUKSI OUTPUT (PENTING):\n" .
              "- Hasilkan ringkasan dalam format Markdown yang rapi.\n" .
              "- JANGAN berikan kalimat pembuka, kata pengantar, atau basa-basi (seperti 'Berikut adalah ringkasan...').\n" .
              "- JANGAN ada teks apapun sebelum Judul Utama.\n" .
              "- Karakter pertama dari responmu HARUS berupa tanda pagar (#) untuk Judul Utama.";

    $summary = $this->openai->generateContent("", $prompt, $this->modelOpenaiSummary, [
      'temperature' => 0.7
    ]);

    //* 3. Update DB
    $session->update([
      'research_raw_tavily' => json_encode($tavilyResult),
      'research_summary'    => $summary
    ]);

    return [
      'uuid'                => $input['uuid'],
      'research_raw_tavily' => $tavilyResult,
      'research_summary'    => $summary,
      'cleaned'             => $textForResearch
    ];
  }

  /**
   * Langkah 5: Writing (Generation & Review)
   */
  public function step3_doWrite(array $input)
  {
    $uuid    = $input['uuid'] ?? null;
    $summary = $input['research_summary'] ?? null;
    $query   = ArticleAgent::find($input['uuid']);
    if (!$query) {
      throw new Exception("Query not found.");
    }

    if (!empty($summary)) {
      $query->update(['research_summary' => $summary]);
      $query->refresh();
    }
    if (!$query->research_summary) {
      throw new Exception("Research summary missing. Cannot generate article.");
    }

    $selectedData = $query->selected_option_data;
    $systemPrompt = "Aku ingin Kamu menjawab hanya dalam bahasa Indonesia.\n" .
                    "Aku ingin Kamu bertindak sebagai SEO yang sangat mahir dan penulis konten berkualitas tinggi yang berbicara dan menulis dengan lancar dalam bahasa Indonesia.\n" .
                    "Aku ingin Kamu berpura-pura dapat menulis konten dengan sangat baik dalam bahasa Indonesia sehingga dapat mengungguli situs web lain.\n" .
                    "Tugas Kamu adalah menulis artikel yang dimulai dengan Judul SEO {$selectedData['title']}.\n" .
                    "Tulis ulang konten dan sertakan daftar, tabel, atau subjudul menggunakan kata kunci terkait.\n" .
                    "Artikel harus 100% unik dan bebas plagiarisme.\n" .
                    "Artikel harus terdiri dari 300 hingga 1000 kata.\n" .
                    "1 subjudul minimal 2 paragraf.\n" .
                    "1 paragraf maksimal 4 kalimat.\n" .
                    "1 kalimat maksimal 20 kata.\n" .
                    "Sebarkan kata kunci fokus di awal, tengah, dan akhir artikel.\n" .
                    "Jangan menambahkan kesimpulan di akhir artikel.\n" .
                    "Semua hasil harus dalam bahasa Indonesia dan harus 100% gaya penulisan manusia.\n" .
                    "Perbaiki masalah tata bahasa dan ubah ke kalimat aktif.\n" .

                    "INSTRUKSI STRUKTUR HTML (PENTING):\n" .
                    "- Gunakan tag <h2> untuk sub-judul utama. Jangan gunakan <h1> (karena itu untuk judul halaman).\n" .
                    "- Gunakan tag <h3> jika butuh sub-sub-judul yang lebih mendetail.\n" .
                    "- Gunakan tag <p> untuk setiap paragraf.\n" .
                    "- Gunakan tag <strong> untuk penekanan/bold dan <em> untuk miring.\n" .
                    "- Gunakan 1 tag <a> dengan attribute target='_blank' untuk membuat hyperlink eksternal ke salah satu halaman web sumber (tidak harus semua sumber).\n" .
                    "- Jika ada poin-poin, WAJIB gunakan tag <ul> untuk list tidak berurutan dan <ol> untuk list berurutan (langkah-langkah). Bungkus setiap item dengan <li>.\n" .
                    "- Jika menyajikan data perbandingan atau statistik, buatlah dalam format HTML <table>.\n" .
                    "- Sertakan <thead> untuk baris judul (header) dan <tbody> untuk isinya.\n" .
                    "- Gunakan <th> untuk sel header dan <td> untuk sel data.\n" .
                    "- Jika ada kutipan penting atau highlight, gunakan tag <blockquote>.\n" .
                    "- Jangan sertakan markdown code block (```html), berikan raw string saja.\n" .
                    "- JANGAN gunakan karakter newline (\\n) di dalam string HTML untuk formatting kode. Tulis HTML sebagai satu baris panjang (minified) agar rapi di JSON.\n" .

                    "Pada masing-masing sub-heading buatkan Prompt Untuk Generate Gambar di Gemini dalam tanda kurung yang terkait dengan topik tersebut tepat di bawah Sub-Heading. Dan buatkan Prompt untuk Generate Gambar Cover Artikel tepat di atas paragraf pertama dalam tanda kurung.\n" .
                    "INSTRUKSI PROMPT GAMBAR:\n" .
                    "- Pada setiap sub-heading (<h2>), buatkan Prompt Gambar Gemini terkait topik tersebut.\n" .
                    "- Buatkan juga 1 Prompt Gambar Cover di bagian paling atas artikel.\n" .
                    "- WAJIB gunakan format teks persis seperti ini: [Gambar: Deskripsi detail visual disini...]\n" .
                    "- Letakkan teks prompt tersebut di dalam tag <p> tersendiri atau tepat di bawah <h2>.\n" .

                    "Selain itu, buatlah meta deskripsi yang ramah SEO dengan maksimal 150 karakter berdasarkan Judul dan Focus Keyword.\n" .
                    "Jangan buat focus keyword menjadi bold di dalam artikel.\n" .
                    "Hindari penggunaan hiperbola dan frasa yang menyerupai kecerdasan buatan (AI) seperti:\n" .
                    "yang mendalam, di era teknologi, sebagai kesimpulan, komunitas global, bukan sekadar, tetapi juga, lebih dari sekadar, memukau, kombinasi antara, bukan hanya, tetapi juga, tidak hanya, karakter unik, menyentuh, andal, emosional, semangatnya dalam, perpaduan antara, yang megah, batin, memikat, yang berbeda, keindahan abadi, dalam industri, terus berkembang, memperdalam pengalaman, ikonik, memperkuat identitas, menyatu sempurna, menyumbangkan, dunia fantasi, magis, penuh nuansa, menghadirkan elemen, layak untuk dicoba, solusi tepat bagi, tak lekang oleh waktu, dalam setiap detailnya, memberikan kesan, menjadi pilihan utama, bernilai estetika tinggi, menawarkan sensasi berbeda, menyatukan elemen, menjadi daya tarik tersendiri, cita rasa tinggi, sentuhan akhir yang sempurna, menggambarkan keanggunan, menginspirasi siapa saja, bagi para pecinta, didesain khusus untuk, tidak pernah gagal dalam, mendefinisikan ulang arti, berpadu dengan harmoni, dirancang dengan penuh perhatian, mengajak pembaca menyelami, pesonanya tak terbantahkan, perjalanan penuh makna, bertransformasi menjadi, melintasi batas imajinasi, tak sekadar tampilan luar, semakin memperkaya pengalaman, lebih dari sekadar, dalam konteks, mengguncang, menonjol, solusi praktis, nuansa khas, pusat perhatian, sorotan, penuh emosi.\n" .
                    "Return format: JSON object with keys: 'content' (body only, no html/head tags), 'meta_description', 'excerpt'.\n" .
                    "IMPORTANT: Ensure all double quotes inside the 'content' value are properly escaped so the JSON remains valid.";

    $userPrompt = "Title: {$selectedData['title']}\n" .
                  "Focus Keyword: {$selectedData['keyword']}\n" .
                  "Text to Rewrite:\n{$query->research_summary}\n";

    //* 2. Call ChatGPT
    $rawResponse = $this->openai->generateContent($systemPrompt, $userPrompt, $this->modelOpenaiWriting, [
      // 'temperature'       => 0.7,
      // 'presence_penalty'  => 0.4,
      // 'frequency_penalty' => 0.3,
      // 'top_p'             => 0.9
    ]);
    if (!$rawResponse) {
      throw new Exception("Failed to generate article from Models.");
    }

    $decoded = json_decode($rawResponse, true);
    if (is_array($decoded)) {
      $decoded['title']   = $selectedData['title'] ?? '';
      $decoded['keyword'] = $selectedData['keyword'] ?? '';
      $contentToSave = json_encode($decoded);
    } else {
      $contentToSave = $rawResponse;
    }

    //* 3. Update DB
    $query->update(['generated_article_content' => $contentToSave]);

    return [
      'uuid'   => $input['uuid'],
      'result' => $decoded ? $decoded : $rawResponse
    ];
  }

  /**
   * Retrieve Session Data
   */
  public function detail(array $input)
  {
    $query = ArticleAgent::where('id_parent', $input['id_article'])->first();
    if (!$query) {
      throw new Exception("Article Agent not found.");
    }

    $data = [
      'uuid'                      => $query->id ?? null,
      'user_input_topic'          => $query->user_input_topic ?? null,
      'brainstorming_options'     => $query->brainstorming_options ?? null,
      'selected_option_data'      => $query->selected_option_data ?? null,
      'research_raw_tavily'       => json_decode($query->research_raw_tavily ?? '', true) ?: null,
      'research_summary'          => $query->research_summary ?? null,
      'generated_article_content' => json_decode($query->generated_article_content ?? '', true) ?: null,
    ];

    return $data;
  }
}
