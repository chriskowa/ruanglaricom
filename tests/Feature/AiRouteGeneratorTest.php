<?php

namespace Tests\Feature;

use Tests\TestCase;

class AiRouteGeneratorTest extends TestCase
{
    public function test_ai_route_generator_returns_waypoints_for_pistol_shape()
    {
        $response = $this->postJson(route('tools.buat-rute-lari.ai-generate'), [
            'lat' => -6.175392,
            'lng' => 106.827153,
            'target_distance' => 5,
            'shape' => 'pistol',
            'scale_factor' => 1.0,
            'use_ai' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json();
        $this->assertIsArray($data['waypoints']);
        $this->assertGreaterThan(3, count($data['waypoints']));
    }

    public function test_ai_route_generator_returns_waypoints_for_circle_shape()
    {
        $response = $this->postJson(route('tools.buat-rute-lari.ai-generate'), [
            'lat' => -6.175392,
            'lng' => 106.827153,
            'target_distance' => 10,
            'shape' => 'circle',
            'scale_factor' => 1.0,
            'use_ai' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json();
        $this->assertIsArray($data['waypoints']);
    }
}
