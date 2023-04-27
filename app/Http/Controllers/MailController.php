<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use Notification;
use App\Models\MasterMidTid;
use App\Models\ReportCpopGenerate;
use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

ini_set('max_execution_time', 0);

class MailController extends Controller
{
    public function index(){

        $dateD1    = date('d-m-Y', strtotime("-1 day", strtotime(date("d-m-Y"))));
        $dateD2    = date('d-m-Y', strtotime("-2 day", strtotime(date("d-m-Y"))));
        $dateD3    = date('d-m-Y', strtotime("-3 day", strtotime(date("d-m-Y"))));
        $dateD6    = date('d-m-Y', strtotime("-6 day", strtotime(date("d-m-Y"))));
        $dateD7    = date('d-m-Y', strtotime("-6 day", strtotime(date("d-m-Y"))));

        $data = MasterMidTid::with('CPOP')->where('Status', '=', '1')->get();
        // dd($data);
        foreach($data as $key => $val){
            $data = array('name'=>"No Reply TBS",
                    'dateD1'    => $dateD1,
                    'dateD2'    => $dateD2,
                    'dateD3'    => $dateD3,
                    'dateD6'    => $dateD6,
                    'dateD7'    => $dateD7,
                    'val'       => $val
                );
            if($val->Type == '7031'){
                $Type = 'emailunsettled.mail';
                $path = Storage::disk('unsettled-D1')->path($val->Path);
                $datemail = 'D+1';
            }
            if($val->Type == '7032'){
                $Type =  'emailunsettled.mailD3';
                $path = Storage::disk('unsettled-D3')->path($val->Path);
                $datemail = 'D+3';
            }
            if($val->Type == '7034'){
                $Type =  'emailunsettled.mailD7';
                $path = Storage::disk('unsettled-D7')->path($val->Path);
                $datemail = 'D+7';
            }
            // dd($path);
            // DB::beginTransaction();
            try{
                Mail::send($Type, $data, function($message) use($val, $path, $datemail) {
                    $message->to('farhan.farhan@bankmega.com', 'Farhan')->subject
                       ('Unsettled Transaction Report ' . $datemail . ' ' . $val->TID . ' ' . $val->Merch_Num . ' ' . $val->CPOP->Merch_Name);
                       $message->attach($path);
                       $message->from('smtpcds@bankmega.com','No Reply TBS');
                });
                $Status = 2;
            }catch(\Throwable $e){
                $Status  = 3;
            }
            $query = "UPDATE mastermidtid
            SET
            Status = '$Status'
            WHERE
            TID = '$val->TID' AND
            Txn_date = '$val->Txn_date' AND
            Merch_Num = '$val->Merch_Num'";
            $data = DB::connection('sqlsrv')->select($query);
        }
        echo "Email Sent with attachment. Check your inbox.";
    }

    public function notification()
    {
        $email = 'farhan.farhan@bankmega.com';
        $data = [
            'title' => 'Selamat datang!',
            'url' => '/',
        ];

        Mail::to($email)->send(new SendMail($data));
        return 'Berhasil mengirim email!';
    }

    public function attachment() {

        $dateD1    = date('d-m-Y', strtotime("-1 day", strtotime(date("d-m-Y"))));
        $dateD2    = date('d-m-Y', strtotime("-2 day", strtotime(date("d-m-Y"))));
        $dateD3    = date('d-m-Y', strtotime("-3 day", strtotime(date("d-m-Y"))));
        $dateD6    = date('d-m-Y', strtotime("-6 day", strtotime(date("d-m-Y"))));
        $dateD7    = date('d-m-Y', strtotime("-6 day", strtotime(date("d-m-Y"))));

        $data = MasterMidTid::where('Status', '=', '1')->get();
        // dd($data);
        foreach($data as $key => $val){
            $data = array('name'=>"No Reply TBS",
                    'dateD1'    => $dateD1,
                    'dateD2'    => $dateD2,
                    'dateD3'    => $dateD3,
                    'dateD6'    => $dateD6,
                    'dateD7'    => $dateD7,
                    'val'       => $val
                );
            if($val->Type == '7031'){
                $Type = 'mail';
                $path = Storage::disk('unsettled-D1')->get($val->Path);
                $datemail = 'D+1';
            }
            if($val->Type == '7032'){
                $Type =  'mailD3';
                $path = Storage::disk('unsettled-D3')->get($val->Path);
                $datemail = 'D+3';
            }
            if($val->Type == '7034'){
                $Type =  'mailD7';
                $path = Storage::disk('unsettled-D7')->get($val->Path);
                $datemail = 'D+7';
            }
            // dd($path);
            DB::beginTransaction();
            try{
                Mail::send($Type, $data, function($message) use($val, $path, $datemail) {
                    $message->to('farhan.farhan@bankmega.com', 'Farhan')->subject
                       ('Unsettled Transaction Report ' . $datemail . ' ' . $val->TID . ' ' . $val->Merch_Num . ' ' . $val->Merch_Name);
                       $message->attach($path);
                       $message->from('smtpcds@bankmega.com','No Reply TBS');
                });
                $Status = 2;
            }catch(\Throwable $e){
                $Status  = 3;
            }
            $query = "UPDATE mastermidtid
            SET
            Status = '$Status'
            WHERE
            TID = '$val->TID' AND
            Txn_date = '$val->Txn_date' AND
            Merch_Num = '$val->Merch_Num'";
            $data = DB::connection('sqlsrv')->select($query);

        }

        // 1. GET DATA STATUS 1
        // 2. FOREACH DATA
            // 2.0 data sesui type, untuk parameter ke template email
            // 2.1. SESUAIKAN PRAMETER SESUAI DATA DI DB
            // 2.2 ketika data berhasil di send email update status 2
            // 2.3 ketika gagal update status 3

        echo "Email Sent with attachment. Check your inbox.";
    }

    public function sendsettle()
    {
        $dateD1 = date('d-m-Y', strtotime("-1 day", strtotime(date("d-m-Y"))));

        $data = ReportCpopGenerate::where('Status', '=', '1')->get();
        // dd($data);
        foreach($data as $key => $val){
            // dd($val);
            $data = array('name' => "No Reply TBS",
                    'dateD1'     => $dateD1,
                    'val'        => $val
            );
            $Type = 'emailsettled.mails';
            $path = Storage::disk('settled')->path($val->Path);
            // dd($path);
            try{
                Mail::send($Type, $data, function($message) use($val, $path) {
                    $message->to('farhan.farhan@bankmega.com', 'Farhan')->subject
                       ('Daily Transaction Report ' . ' ' . $val->MID . ' ' . $val->ACCT_NAME);
                       $message->attach($path);
                       $message->from('smtpcds@bankmega.com','No Reply TBS');
                });
                $Status = 2;
            }catch(\Throwable $e){
                // dd($e->getMessage());
                $Status = 3;
            }
            $query = "UPDATE reportcpop_generate
            SET
            Status = '$Status'
            WHERE
            MID = '$val->MID' AND
            TID = '$val->TID' AND
            ACCT_NAME = '$val->ACCT_NAME'";
            $data = DB::connection('sqlsrv')->select($query);
        }
        echo "Email Sent with attachment. Check your inbox.";
    }
}
