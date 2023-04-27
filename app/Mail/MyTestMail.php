<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyTestMail extends Mailable
{
    use Queueable, SerializesModels;

    private $name;
    private $dateD1;
    private $dateD2;
    private $val;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($val)
    {
        $this->name   = $val->name;
        $this->dateD1 = $val->dateD1;
        $this->dateD2 = $val->dateD2;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail')
        ->attach(public_path('app/generate-report/unsettled/D1'), [
             'as' => 'Report_Unsettled_20220831_50019039_COFFEE BEAN TEA LEAF R        .xslx',
             'mime' => 'Report_Unsettled_20220831_50019039_COFFEE BEAN TEA LEAF R        .xslsx',
        ]);
    }
}
