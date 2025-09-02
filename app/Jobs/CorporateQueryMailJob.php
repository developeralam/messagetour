<?php

namespace App\Jobs;

use App\Models\CorporateQuery;
use App\Mail\CorporateQueryMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CorporateQueryMailJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    public $email;
    public CorporateQuery $query;

    /**
     * Create a new job instance.
     */
    public function __construct($email, CorporateQuery $query)
    {
        $this->email = $email;
        $this->query = $query;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            setMailConfig();
            Mail::to($this->email)
                ->send(new CorporateQueryMail($this->query));
        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
}
