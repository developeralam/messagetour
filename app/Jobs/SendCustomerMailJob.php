<?php

namespace App\Jobs;

use App\Mail\CustomerMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCustomerMailJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    public $to_email;
    public $bcc_emails;
    public $subject;
    public $email_body;

    /**
     * Create a new job instance.
     */
    public function __construct($to_email, $bcc_emails, $subject, $email_body)
    {
        $this->to_email = $to_email;
        $this->bcc_emails = $bcc_emails;
        $this->subject = $subject;
        $this->email_body = $email_body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            setMailConfig();
            Mail::to($this->to_email)
                ->bcc($this->bcc_emails)
                ->send(new CustomerMail($this->subject, $this->email_body));
        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
}
