<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class CorporateQueryInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $fileContent;
    public $fileName;
    public $mimeType;

    /**
     * Create a new message instance.
     */
    public function __construct($fileContent, $fileName, $mimeType)
    {
        $this->fileContent = $fileContent;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Corporate Query Invoice Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.corporate-query-invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn() => base64_decode($this->fileContent),
                $this->fileName
            )->withMime($this->mimeType),
        ];
    }
}
