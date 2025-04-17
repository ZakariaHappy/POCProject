<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MergeProposalCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $pullRequestUrls;

    public function __construct($pullRequestUrls)
    {
        $this->pullRequestUrls = $pullRequestUrls;
    }

    public function build()
    {
        return $this->subject('Nieuwe Mergevoorstellen Aangemaakt')->view('emails.merge_proposals');
    }
}
