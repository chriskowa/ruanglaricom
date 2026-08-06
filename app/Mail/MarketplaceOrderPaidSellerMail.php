<?php

namespace App\Mail;

use App\Models\Marketplace\MarketplaceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketplaceOrderPaidSellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(MarketplaceOrder $order)
    {
        $this->order = $order->load(['items.product.primaryImage', 'buyer', 'seller']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Baru Masuk! Invoice: ' . $this->order->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketplace.seller-order-paid',
        );
    }
}
