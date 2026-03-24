<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageTemplate;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Modern Homepage',
                'slug' => 'modern-homepage',
                'description' => 'Modern homepage template with hero section and features',
                'view_path' => 'templates.homepage.modern',
                'sections' => [
                    ['key' => 'headline', 'label' => 'Headline', 'type' => 'text'],
                    ['key' => 'subheadline', 'label' => 'Subheadline', 'type' => 'text'],
                    ['key' => 'hero_image', 'label' => 'Hero Image', 'type' => 'image'],
                    ['key' => 'cta_text', 'label' => 'CTA Text', 'type' => 'text'],
                    ['key' => 'cta_link', 'label' => 'CTA Link', 'type' => 'text'],
                ],
                'is_active' => true,
                'is_homepage' => false
            ],
            [
                'name' => 'Classic Homepage',
                'slug' => 'classic-homepage',
                'description' => 'Classic homepage template with clean design',
                'view_path' => 'templates.homepage.classic',
                'sections' => [
                    ['key' => 'welcome_text', 'label' => 'Welcome Text', 'type' => 'textarea'],
                    ['key' => 'featured_image', 'label' => 'Featured Image', 'type' => 'image'],
                ],
                'is_active' => true,
                'is_homepage' => false
            ],
            [
                'name' => 'Event Discovery Homepage',
                'slug' => 'event-discovery-homepage',
                'description' => 'Conversion-focused homepage for event discovery',
                'view_path' => 'templates.homepage.event-discovery',
                'sections' => [
                    ['key' => 'headline', 'label' => 'Headline', 'type' => 'text'],
                    ['key' => 'subheadline', 'label' => 'Subheadline', 'type' => 'text'],
                    ['key' => 'featured_events', 'label' => 'Featured Events', 'type' => 'json'],
                ],
                'is_active' => true,
                'is_homepage' => true
            ]
        ];

        foreach ($templates as $template) {
            PageTemplate::updateOrCreate(['slug' => $template['slug']], $template);
        }
    }
}