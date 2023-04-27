<?php

namespace App\Console\Commands;

use App\Exports\CpopExport;
use App\Models\CPOP;
use App\Models\MasterMidTid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportUnsettled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:report-unsettled';

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
    public function handle(Request $request)
    {
        $modify = $request->date;

        // dd($req);
        if(empty($request->date)){
            $modify = 0;
        }
        $date       = Carbon::now()->modify(''.$modify.' day')->format('Ymd');
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
            $type2      = trim($type[0], 'CPOP');

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
        $this->generateCpopExcel();
        return response()->json('anjay');
        // return Excel::download(new CpopExport, 'cpop.xlsx');
	}

    public function generateCpopExcel()
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
}
