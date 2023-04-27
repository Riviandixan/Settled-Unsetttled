<?php

namespace App\Http\Controllers;

use App\Exports\CpopExport;
use App\Exports\ReportExport;
use App\Models\CPOP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\MasterMidTid;
use App\Models\MERCHTXNR;
use App\Models\ReportCpopGenerate;

ini_set('max_execution_time', 0);


class CPOPController extends Controller
{
    public function uploadUnsettled(Request $request)
    {
        // GET CPOP7031_YYYYMMDD
        // GET CPOP7032_YYYYMMDD
        // GET CPOP7034_YYYYMMDD
        $modify = $request->date;

        // dd($req);
        if(empty($request->date)){
            $modify = 0;
        }
        $date       = Carbon::now()->modify(''.$modify.' day')->format('Ymd');
        // dd($date);
        $setDate    = Carbon::now()->modify(''. $modify . ' day')->format('Y-m-d');

        // $code = 'CPOP7032_';
        $listCode = ['CPOP7031_','CPOP7032_', 'CPOP7034_'];
        $successfile = [];
        $failedfile = [];
        foreach ($listCode as  $code) {

            $filename   = $code. $date . '.csv';
            $exist      = CPOP::Where('filename', $filename)->first();
            if(!empty($exist)){
                Continue;
            }

            // get file from FTP
            $path       = '/u/cardpro/export/cpop/';
            $fullpath   = $path . $filename;

            $ftp        = Storage::disk('ftpcpop')->get($fullpath);
            // put to local
            $local_path = 'cpop/';
            $get_file   = Storage::disk('local')->put($local_path . $filename, $ftp);
            $type       = explode('_', $code);
                    

            if($get_file){
                $local_path = 'app\cpop\\' . $filename; //ubah seperti di atas

                $filepath = storage_path($local_path);
                $file = fopen($filepath, "r");
                $importData_arr = array();

                $i = 0;
                while(($filedata = fgetcsv($file, 1000, ";")) !== FALSE){
                    // dd($file);
                    $getType = explode('_', $filedata[0]);
                    $fileType = trim($getType[0],"HCPOP");

                    $num = count($filedata);

                    if($i == 0){
                        $i++;
                        continue;
                    }
                    for($c = 0; $c < $num; $c++){
                        $importData_arr[$i][] = $filedata[$c];
                    }
                    $i++;
                }
                fclose($file);

                $j = 0;
                foreach(@$importData_arr as $importData){
                    if(!empty($importData[1])){
                        $j++;
                        // dd($importData);
                        DB::beginTransaction();
                        try{
                            CPOP::create([
                                'D' => $importData[0],
                                'No' => $importData[1],
                                'Type' => $type2,
                                'Txn_date' => $importData[2],
                                'TID' => $importData[3],
                                'Merch_Num' => $importData[4],
                                'Merch_Name' => $importData[5],
                                'City' => $importData[6],
                                'Txn_code' => $importData[7],
                                'Txn_sts_code' => $importData[8],
                                'Txn_stts' => $importData[9],
                                'Txn_numb' => $importData[10],
                                'Txn_total' => $importData[11]*1,
                                'point' => $importData[12],
                                'filename'  => $filename,
                                'settlementDate' => $setDate
                            ]);

                            DB::commit();
                        }catch(\Exception $e){
                            // throw $th;
                            dd($e);
                            DB::rollBack();
                        }
                    }
                }
                $this->fidingMerchentMID();

                $successfile[$code]=$filename;
            // return response()->json([
            //     'message'   => "file record successfully uploaded"
            // ]);
            }else{
                $failedfile[$code]=$filename;
                // no file was uploaded
                // throw new \Exception('No fie was uploaded',
                // Response::HTTP_BAD_REQUEST);
            }    # code...
        }
        return response()->json([
            'successfile'=>$successfile,
            'failedfile'=>$failedfile
        ]);
    }

    public function fidingMerchentMID()
	{
        $query = "SELECT A.Merch_Num, A.TID, A.Txn_date, A.Type FROM t_unsettled A
        GROUP BY A.Merch_Num, A.TID,
        A.Txn_date, A.Type
        ";
        $data = DB::connection('sqlsrv')->select($query);
        foreach($data as $value){
            // dd($data);
            DB::beginTransaction();
            try{
                MasterMidTid::updateOrCreate([
                    'Txn_date'  => $value->Txn_date,
                    'TID'       => $value->TID,
                    'Merch_Num' => $value->Merch_Num,
                    'Type'      => $value->Type,
                    // 'Path'      => 'Excel'
                ],
                [
                    'Txn_date'  => $value->Txn_date,
                    'TID'       => $value->TID,
                    'Merch_Num' => $value->Merch_Num,
                    'Type'      => $value->Type,
                    'Status'    => '0',
                    'Path'      => 'Excel'
                ]
                );
                DB::commit();
            }catch(\Exception $e){
                // throw $th;
                dd($e);
                DB::rollBack();
            }
        }
        $this->generateCpopUnsettled();
        return response()->json('anjay');
	}

    public function generateCpopUnsettled()
	{
        $data = MasterMidTid::where('Status', '=', '0')->get();
        // dd($data);
        foreach($data as $key => $val){
            // dd($val);
            try {
                if($val->Type == '7031'){
                    $path = 'unsettled-D1';
                }
                if($val->Type == '7032'){
                    $path = 'unsettled-D3';
                }
                if($val->Type == '7034'){
                    $path = 'unsettled-D7';
                }
                $filename = 'Report_Unsettled' . '_' . $val->Txn_date . '_' . $val->TID . '_' . $val->CPOP->Merch_Name . '.xlsx';
                Excel::store(new CpopExport($val), 'Report_Unsettled' . '_' . $val->Txn_date . '_' . $val->TID . '_' . $val->CPOP->Merch_Name . '.xlsx', $path);
                $Status = 1;

            } catch (\Throwable $e) {
                // throw $e;
                $Status = 2;
                // dd($e);
            }

            $query = "UPDATE t_unsettled_staging
            SET
            Status = '$Status',
            Path = '$filename'
            WHERE
            TID = '$val->TID' AND
            Txn_date = '$val->Txn_date' AND
            Merch_Num = '$val->Merch_Num'
            ";
            $data = DB::connection('sqlsrv')->statement($query);
        }
        $this->sendEmailUnsettled();
        return response()->json('slebew');
	}

    public function sendEmailUnsettled()
    {
        $dateD1    = date('d-m-Y', strtotime("-1 day", strtotime(date("d-m-Y"))));
        $dateD2    = date('d-m-Y', strtotime("-2 day", strtotime(date("d-m-Y"))));
        $dateD3    = date('d-m-Y', strtotime("-3 day", strtotime(date("d-m-Y"))));
        $dateD6    = date('d-m-Y', strtotime("-6 day", strtotime(date("d-m-Y"))));
        $dateD7    = date('d-m-Y', strtotime("-6 day", strtotime(date("d-m-Y"))));

        $data  = MasterMidTid::with('CPOP')->where('Status', '=', '1')->get();
        $data2 = CPOP::select('Merch_Name')->get();
        // dd($data2);

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
            DB::beginTransaction();
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
            $query = "UPDATE t_unsettled_staging
            SET
            Status = '$Status'
            WHERE
            TID = '$val->TID' AND
            Txn_date = '$val->Txn_date' AND
            Merch_Num = '$val->Merch_Num'";
            $data = DB::connection('sqlsrv')->statement($query);
        }
        echo "Email Sent with attachment. Check your inbox.";
    }

    public function uploadSettled(Request $request)
    {
        // dd('hai');
        if(empty($request->date)){
            $modify = -1;
        }
        $date = Carbon::now()->modify(''.$modify.' day')->format('dmY');
        // dd($date);
        $listReport = ['output_'];
        $successfile = [];
        $failedfile = [];
        foreach ($listReport as $report){
            $filename = $report . $date . '.csv';
            // $exist = MERCHTXNR::where('filename', $filename)->first();
            // dd($exist);
            if(!empty($exist)){
                Continue;
            }

            // get file from FTP
            $path       = '/MERCTXNR_CSV/';
            // $filename = 'MERCHTXNR_25082022.xlsx';
            $fullpath   = $path . $filename;
            // dd($fullpath);
            $ftp        = Storage::disk('reportcpop')->get($fullpath);
            // put to local
            $local_path = 'excelreport/';
            $get_file   = Storage::disk('local')->put($local_path . $filename, $ftp);

            if($get_file){

                $local_path = 'app\excelreport\\' . $filename;
                $filepath = storage_path($local_path);
                $file = fopen($filepath, "r");
                $MERCHTNR_ARR = array();

                $i = 0;
                while(($filedata = fgetcsv($file, 1000, ",")) !== FALSE){

                    $num = count($filedata);

                    if($i == 0){
                        $i++;
                        continue;
                    }
                    for($c = 0; $c < $num; $c++){
                        $MERCHTNR_ARR[$i][] = $filedata[$c];
                    }
                    $i++;
                    // dd($filedata);
                }
                fclose($file);

                $j = 0;
                foreach(@$MERCHTNR_ARR as $MERCHTNR){
                    if(!empty($MERCHTNR[1])){
                        $j++;
                        // dd($MERCHTNR);
                        DB::beginTransaction();
                        try{
                            MERCHTXNR::create([
                            'MID' => trim($MERCHTNR[0]),
                            'PARENT' => trim($MERCHTNR[1]),
                            'ORIG_MID' => trim($MERCHTNR[2]),
                            'TID' => trim($MERCHTNR[3]),
                            'POS_BATCH' => trim($MERCHTNR[4]),
                            'NBR_TXN' => trim($MERCHTNR[5]),
                            'SETTLE_DTE' => trim($MERCHTNR[6]),
                            'SETTLE_TME' => trim($MERCHTNR[7]),
                            'TXN_DTE' => trim($MERCHTNR[8]),
                            'TXN_TIME' => trim($MERCHTNR[9]),
                            'CARD_NBR' => trim($MERCHTNR[10]),
                            'CARD_TYPE' => trim($MERCHTNR[11]),
                            'AUTH_CODE' => trim($MERCHTNR[12]),
                            'MDR' => trim($MERCHTNR[13]),
                            'TXN_AMT' => trim($MERCHTNR[14]),
                            'DISC_AMT' => trim($MERCHTNR[15]),
                            'NET_AMT' => trim($MERCHTNR[16]),
                            'TXN_DESC' => trim($MERCHTNR[17]),
                            'BANK_NAME' => trim($MERCHTNR[18]),
                            'ACCT_NBR' => trim($MERCHTNR[19]),
                            'ACCT_NAME' => trim($MERCHTNR[20]),
                            'ENTRY_MODE' => trim($MERCHTNR[21]),
                            'FLAT_FEE' =>trim($MERCHTNR[22]),
                            ]);
                            DB::commit();
                        }catch(\Exception $e){
                            dd($e);
                            DB::rollBack();
                        }
                    }
                }
                $this->fidingCpopGenerate();

                $successfile[$report]=$filename;
            }else{
                $failedfile[$report]=$filename;
            }
        }
        return response()->json([
            'successfile' => $successfile,
            'failedfile'  => $failedfile
        ]);
    }

    public function fidingCpopGenerate()
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
        // dd($data);
        foreach($data as $value){
            // dd($value);
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
        }
        $this->sendEmailSettled();
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


