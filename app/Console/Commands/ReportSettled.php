<?php

namespace App\Console\Commands;

use App\Exports\ReportExport;
use App\Models\MasterMidTid;
use App\Models\MERCHTXNR;
use App\Models\ReportCpopGenerate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportSettled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:report-settled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    // public function handle()
    // {
    //     $modify     = -4;
    //     $date       = Carbon::now()->modify(''.$modify.' day')->format('dmY');
    //     $listReport = ['output_'];
    //     $successfile= [];
    //     $failedfile = [];
    //     foreach ($listReport as $report){
    //         // dd($report);
    //         $filename = $report . $date . '.csv';
    //         // $exist = MERCHTXNR::where('TID', $filename)->first();
    //         if(!empty($exist)){
    //             Continue;
    //         }

    //         // get file from FTP
    //         $path       = '/MERCTXNR_CSV/';
    //         // $filename = 'MERCHTXNR_25082022.xlsx';
    //         $fullpath   = $path . $filename;
    //         // dd($fullpath);
    //         $ftp        = Storage::disk('reportcpop')->get($fullpath);
    //         // put to local
    //         $local_path = 'excelreport/';
    //         $get_file   = Storage::disk('local')->put($local_path . $filename, $ftp);

    //         if($get_file){

    //             $local_path = 'app\excelreport\\' . $filename;
    //             $filepath = storage_path($local_path);
    //             $file = fopen($filepath, "r");
    //             $MERCHTNR_ARR = array();

    //             $i = 0;
    //             while(($filedata = fgetcsv($file, 1024, ",")) !== FALSE){

    //                 $num = count($filedata);

    //                 if($i == 0){
    //                     $i++;
    //                     continue;
    //                 }
    //                 for($c = 0; $c < $num; $c++){
    //                     $MERCHTNR_ARR[$i][] = $filedata[$c];
    //                 }
    //                 $i++;
    //             }
    //             fclose($file);

    //             $j = 0;
    //             foreach(@$MERCHTNR_ARR as $MERCHTNR){

    //                 if(!empty($MERCHTNR[1])){
    //                     $j++;
    //                     // dd($MERCHTNR);
    //                     DB::beginTransaction();
    //                     try{
    //                         MERCHTXNR::create([
    //                         'MID' => trim($MERCHTNR[0]),
    //                         'PARENT' => trim($MERCHTNR[1]),
    //                         'ORIG_MID' => trim($MERCHTNR[2]),
    //                         'TID' => trim($MERCHTNR[3]),
    //                         'POS_BATCH' => trim($MERCHTNR[4]),
    //                         'NBR_TXN' => trim($MERCHTNR[5]),
    //                         'SETTLE_DTE' => trim($MERCHTNR[6]),
    //                         'SETTLE_TME' => trim($MERCHTNR[7]),
    //                         'TXN_DTE' => trim($MERCHTNR[8]),
    //                         'TXN_TIME' => trim($MERCHTNR[9]),
    //                         'CARD_NBR' => trim($MERCHTNR[10]),
    //                         'CARD_TYPE' => trim($MERCHTNR[11]),
    //                         'AUTH_CODE' => trim($MERCHTNR[12]),
    //                         'MDR' => trim($MERCHTNR[13]),
    //                         'TXN_AMT' => trim($MERCHTNR[14]),
    //                         'DISC_AMT' => trim($MERCHTNR[15]),
    //                         'NET_AMT' => trim($MERCHTNR[16]),
    //                         'TXN_DESC' => trim($MERCHTNR[17]),
    //                         'BANK_NAME' => trim($MERCHTNR[18]),
    //                         'ACCT_NBR' => trim($MERCHTNR[19]),
    //                         'ACCT_NAME' => trim($MERCHTNR[20]),
    //                         'ENTRY_MODE' => trim($MERCHTNR[21]),
    //                         'FLAT_FEE' =>trim($MERCHTNR[22])
    //                         ]);
    //                         DB::commit();
    //                     }catch(\Exception $e){
    //                         dd($e);
    //                         DB::rollBack();
    //                     }
    //                 }
    //             }
    //             $this->fidingCpopGenerate();

    //             $successfile[$report]=$filename;
    //         }else{
    //             $failedfile[$report]=$filename;
    //         }
    //     }
    //     return response()->json([
    //         'successfile' => $successfile,
    //         'failedfile'  => $failedfile
    //     ]);
    // }

    public function handle()
    {
        $query = "SELECT
        CAST(A.MID AS varchar(100)) AS MID,
        CAST(A.TID AS varchar(100)) AS TID,
        CAST(A.ACCT_NAME AS varchar(100)) AS ACCT_NAME,
        CAST(SUBSTRING(A.TXN_DTE, 1,10)AS varchar(100)) AS TXN_DTE
        FROM t_settledds A
        GROUP BY CAST(A.MID AS varchar(100)),
		CAST(A.TID AS varchar(100)),
        CAST(A.ACCT_NAME AS varchar(100)),
		CAST(SUBSTRING(A.TXN_DTE, 1,10) AS varchar(100))
        ";
        $data = DB::connection('sqlsrv')->select($query);
        foreach($data as $value){
            DB::beginTransaction();
            try{
                ReportCpopGenerate::updateOrCreate([
                    'MID'        => $value->MID,
                    'TID'        => $value->TID,
                    'ACCT_NAME'  => $value->ACCT_NAME,
                    // 'TXN_DTE'    => $value->TXN_DTE
                    // 'Path'
                ],
                [
                    'MID'        => $value->MID,
                    'TID'        => $value->TID,
                    'ACCT_NAME'  => $value->ACCT_NAME,
                    'TXN_DTE'    => $value->TXN_DTE,
                    'Status'     => '0',
                    'Path'       => 'Excel'
                ]
            );
                DB::commit();
            }catch(\Exception $e){
                dd($e);
                DB::rollBack();
            }
        }
        $this->generateReportSettled();
        return response()->json('kuyyy');
    }

    public function generateReportSettled()
    {
        $data = ReportCpopGenerate::with('Txn_desc')->where('Status', '=', '0')->get();
        // dd($data);
        foreach($data as $key => $val){
            // dd($val);
            try {
                $filename = $val->TID . '_' . $val->Txn_desc->TXN_DESC . '.xlsx';
                Excel::store(new ReportExport($val), $val->TID . '_' . $val->Txn_desc->TXN_DESC . '.xlsx', 'settled');
                $Status = 1;
            } catch (\Throwable $e){
                // dd($e);
                $Status = 2;
            }

            $query = "UPDATE t_settleds_staging
            SET
            Status = '$Status',
            Path = '".str_replace("'","''",$filename)."'
            WHERE
            MID = '$val->MID' AND
            TID = '$val->TID' AND
            ACCT_NAME = '$val->ACCT_NAME'
            ";
            $data = DB::connection('sqlsrv')->statement($query);
            // dd($data);
        }
        // $this->sendEmailSettled();
        return response()->json('ntapsss');
    }

    public function sendEmailSettled()
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
            $query = "UPDATE t_settleds_staging
            SET
            Status = '$Status'
            WHERE
            MID = '$val->MID' AND
            TID = '$val->TID' AND
            ACCT_NAME = '$val->ACCT_NAME'";
            $data = DB::connection('sqlsrv')->statement($query);
        }
        echo "Email Sent with attachment. Check your inbox.";
    }
}
