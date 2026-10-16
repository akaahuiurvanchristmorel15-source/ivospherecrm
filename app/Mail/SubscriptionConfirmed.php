<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Confirmation d'abonnement envoyée à l'abonné, avec le récapitulatif PDF en pièce jointe.
 */
class SubscriptionConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription, public ?string $adminEmail = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre abonnement IVOSPHERE '.$this->subscription->plan->name.' est actif',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription_confirmed');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $path = $this->subscription->pdf_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $path)
                ->as('Abonnement-'.$this->subscription->reference.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
