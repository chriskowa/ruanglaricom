<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleAgent extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'id_parent',
        'user_input_topic',
        'strategy',
        'brainstorming_options',
        'selected_option_data',
        'research_raw_tavily',
        'research_summary',
        'generated_article_content',
        'generated_article_content_en',
    ];

    protected $casts = [
        'brainstorming_options'     => 'array',
        'selected_option_data'      => 'array',
        'research_raw_tavily'       => 'array',
        'generated_article_content' => 'array',
        'generated_article_content_en' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'id_parent');
    }
}
