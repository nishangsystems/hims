<?php

namespace App\Console\Commands;

use App\Models\ApplicationForm;
use App\Models\Students;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranzakPaymentFollower extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tranzak_payment_follower';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a check for all pending tranzak transactions and run necessary updates';

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
    public function handle()
    {
        $tranzak_credentials = \App\Models\TranzakCredential::where('campus_id', 0)->first();
        
        \App\Models\PendingTranzakTransaction::each(function($record)use($tranzak_credentials){
            if(cache($tranzak_credentials->cache_token_key) == null or now()->parse(cache($tranzak_credentials->cache_token_expiry_key))->isPast()){
                GEN_TOKEN:
                $response = Http::post(config('tranzak.base').config('tranzak.token'), ['appId'=>$tranzak_credentials->app_id, 'appKey'=>$tranzak_credentials->api_key]);
                if($response->status() == 200){
                    cache([$tranzak_credentials->cache_token_key => json_decode($response->body())->data->token]);
                    cache([$tranzak_credentials->cache_token_expiry_key=>now()->createFromTimestamp(time() + json_decode($response->body())->data->expiresIn)]);
                }
            }

            try{
                if(\App\Models\TranzakTransaction::where('request_id', $record->requestId)->count() > 0){ 
                    $record->delete();
                    return;
                }
    
                if(cache($tranzak_credentials->cache_token_key) == null or now()->parse(cache($tranzak_credentials->cache_token_expiry_key))->isPast()){goto GEN_TOKEN;}
                $url = config('tranzak.base').config('tranzak.transaction_details').$record->requestId;
                $headers = ['Access-Control-Allow-Origin'=> '*',  'Authorization' => "Bearer {{ cache($tranzak_credentials->cache_token_key) }}"];
                $response = Http::withHeaders($headers)->get($url);
                if($response->status() == 200){
                    $data = $response->collect('data');
                    switch($data['status']){
                        case "SUCCESSFUL":
                            $ptransaction = json_decode($record->transaction);
                            $transaction = [
                                'request_id'=>$record->requestId??'', 
                                'payment_id'=>$record->payment_id, 
                                'amount'=>$ptransaction->amount??'', 
                                'currency_code'=>$ptransaction->currencyCode??'', 
                                'purpose'=>$record->payment_purpose??'', 
                                'mobile_wallet_number'=>$ptransaction->mobileWalletNumber??'', 
                                'transaction_ref'=>$ptransaction->mchTransactionRef??'', 
                                'app_id'=>$ptransaction->appId??'', 
                                'transaction_id'=>$ptransaction->transactionId??'', 
                                'transaction_time'=>$ptransaction->transactionTime??'', 
                                'payment_method'=>$ptransaction->payer['paymentMethod']??'', 
                                'payer_user_id'=>$ptransaction->payer['userId']??'', 
                                'payer_name'=>$ptransaction->payer['name']??'', 
                                'payer_account_id'=>$ptransaction->payer['accountId']??'', 
                                'merchant_fee'=>$ptransaction->merchant['fee']??'', 
                                'merchant_account_id'=>$ptransaction->merchant['accountId']??'', 
                                'net_amount_recieved'=>$ptransaction->merchant['netAmountReceived']??''
                            ];
                            if(\App\Models\TranzakTransaction::where($transaction)->count() == 0){
                                $transaction_instance = new \App\Models\TranzakTransaction($transaction);
                                $transaction_instance->save();
                            }else{
                                $transaction_instance = \App\Models\TranzakTransaction::where($transaction)->first();
                            }
                            switch($record->typ){
                                case "PLATFORM":
                                        
                                    $data = [
                                        'student_id'=>$record->student_id, 
                                        'year_id'=>$record->year_id, 'type'=>$record->purpose, 
                                        'item_id'=>$record->payment_id, 'amount'=>$transaction_instance->amount, 
                                        'financialTransactionId'=>$transaction_instance->transaction_id, 'used'=>1
                                    ];
                                    $instance = new \App\Models\Charge($data);
                                    $instance->save();
                                    $student = Students::find($record->student_id);
                                    $message = "Hello ".($student->name??'').", You have successfully paid a sum of ".($transaction_instance->amount??'')." as ".($record->purpose??'')." for ".($transaction_instance->year->name??'')." HIMS.";
                                    $this->sendSmsNotificaition($message, [$student->phone]);
        
                                    $record->delete();
                                    break;
                                case "APPLICATION":
        
                                    $form = ApplicationForm::find($record->form_id);
                                    $form->upate(['transaction_id'=>$transaction_instance->transaction_id]);
                                    break;
                            }
                            break;

                        case "PAYMENT_IN_PROGRESS":
                            return;
                            break;

                        default: 
                            $record->delete();
                            break;
                            
                    }
                }
            }catch(\Throwable $th){
                Log::info("PAYMENT FOLLOWER: ".$th->getMessage());
            }
        });
    }
}
