<?php

namespace Tests\Feature;

use App\Helpers\WhatsApp;
use App\Jobs\ProcessPaidProgramOrder;
use App\Mail\ProgramOrderPaid;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProgramOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_order_paid_mailable_renders_correctly()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 100000,
            'tax' => 0,
            'total' => 100000,
            'payment_method' => 'wallet',
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $coach = User::factory()->create([
            'role' => 'coach',
        ]);

        $program = Program::create([
            'title' => 'Marathon Sub 4 Program',
            'slug' => 'marathon-sub-4-program',
            'duration_weeks' => 12,
            'price' => 100000,
            'coach_id' => $coach->id,
            'program_json' => '{}',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'program_id' => $program->id,
            'program_title' => $program->title,
            'quantity' => 1,
            'price' => 100000,
            'subtotal' => 100000,
        ]);

        $mailable = new ProgramOrderPaid($order);

        $this->assertEquals('Pembelian Program Sukses - RuangLari (' . $order->order_number . ')', $mailable->envelope()->subject);
        
        $rendered = $mailable->render();
        $this->assertStringContainsString('Pembelian Program Sukses!', $rendered);
        $this->assertStringContainsString('Marathon Sub 4 Program', $rendered);
        $this->assertStringContainsString('Rp 100.000', $rendered);
        $this->assertStringContainsString('john@example.com', $rendered);
    }

    public function test_process_paid_program_order_job_sends_email_and_whatsapp()
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 150000,
            'tax' => 0,
            'total' => 150000,
            'payment_method' => 'wallet',
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $coach = User::factory()->create([
            'role' => 'coach',
        ]);

        $program = Program::create([
            'title' => '5K Fast Program',
            'slug' => '5k-fast-program',
            'duration_weeks' => 8,
            'price' => 150000,
            'coach_id' => $coach->id,
            'program_json' => '{}',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'program_id' => $program->id,
            'program_title' => $program->title,
            'quantity' => 1,
            'price' => 150000,
            'subtotal' => 150000,
        ]);

        // Mock WhatsApp AppSettings to ensure WhatsApp::send goes through
        \App\Models\AppSettings::set('whatsapp_is_active', true);
        \App\Models\AppSettings::set('whatsapp_app_key', 'test_key');
        \App\Models\AppSettings::set('whatsapp_auth_key', 'test_auth');

        // We run the job synchronously
        $job = new ProcessPaidProgramOrder($order->id);
        $job->handle();

        Mail::assertSent(ProgramOrderPaid::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Assert database log for WhatsApp was created
        $this->assertDatabaseHas('whatsapp_logs', [
            'to' => '6281234567890',
            'status' => 'sent',
        ]);
    }
}
