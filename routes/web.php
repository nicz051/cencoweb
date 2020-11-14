<?php
use App\Account;
use App\Bill;
use Illuminate\Support\Facades\Input;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::view('/', 'auth.login');

Auth::routes(['register' => false]);

Route::group(['middleware' => ['auth:web','verified']],function(){
    Route::group(['prefix' => '/admin','middleware'=>'admin' ],function(){
        Route::get('/dashboard', 'DashboardController@index');
        Route::get('/usermanagement', 'UserManagementController@index');
        Route::get('/{id}', 'UserManagementController@index');
        Route::post('/save', 'UserManagementController@save');
        Route::get('/deleteUser/{id}', 'UserManagementController@deleteUser');
    });
    Route::group(['prefix' => '/user'],function(){
        Route::get('/dashboard', 'DashboardController@index');
    });

    Route::get('/dashboard', 'DashboardController@index');
    Route::any ( '/search', function(){
        $q = Request::get ( 'q' );
        $user = Account::where('account_number','LIKE','%'.$q.'%')->orWhere('account_name','LIKE','%'.$q.'%')->get();
        // $bill = Bill::where('bills_account_number', 'LIKE','%'.$q.'%')->get();
        $bill = DB::table('bills')
                ->where('bills_account_number', $q)
                ->get();
    
        $transaction = DB::table('transactions')
                // ->whereExists(function ($query) {
                //     $query->select(DB::raw(1))
                //             ->from('bills')
                //             ->whereRaw('transactions.transactions_bill_id = bills.bill_id');
                // })
                ->join('bills', 'transactions.transactions_bill_id', 'bills.bill_id')
                ->where('bills.bills_account_number',$q)
                ->get();
                
        
        if(count($user) > 0)
            return view('pages.searchresults', ['bill' => $bill, 'transaction' => $transaction])->withDetails($user)->withQuery ( $q );
        else return view ('pages.searchresults')->withMessage('No Details found. Try to search again !');
    });
    Route::get('/user_profile', 'PagesController@user_profile');
    Route::get('/data_table/accounts', 'AccountController@index');
    Route::get('/data_table/logs', 'LogsController@index');
    Route::get('/data_table/billing', 'BillController@index');
    Route::get('/data_table/collection', 'TransactionController@index');


});