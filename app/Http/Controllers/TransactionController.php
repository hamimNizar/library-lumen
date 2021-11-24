<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // TODO: Create transaction logic
    public function index(Request $request){
        // dd($request->auth->id);
        if ($request->auth->role == 'admin') {
            $transactions = Transaction::all();
            if ($transactions){
                if(count($transactions) != 0){
                    return response()->json([
                        'success' => true,
                        'message' => 'List all transactions from admin',
                        'data' => ([
                            'transaction' => $transactions
                        ])
                    ], 201);
                }else{
                    return response()->json([
                    'success' => false,
                    'message' => 'No transaction',
                ], 400);
                }
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Server Failure',
                ], 500);
            }
        }else{
            // dd($request->auth->id);
            $transactions = Transaction::where('user_id', $request->auth->id)->first();
            // dd($transactions);
            if ($transactions){
                if(count($transactions->all()) != 0){
                    return response()->json([
                        'success' => true,
                        'message' => 'List transactions for user '. $request->auth->name,
                        'data' => ([
                            'transaction' => $transactions
                        ])
                    ], 201);
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'No transaction',
                    ], 400);
                }
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'No transaction for user '. $request->auth->name
                ], 400);
            }
        }
    }

    public function getTransactionById($transactionId){
        $transactions = Transaction::find($transactionId);
        if ($transactions){
            if(!empty($transactions)){
                return response()->json([
                'success' => true,
                'message' => 'Get transaction by Id',
                'data' => ([
                    'transactions' => $transactions
                ])
                ], 201);
            }elseif(empty($transactions)){
                return response()->json([
                'success' => false,
                'message' => 'there is no transaction with id = '.$transactionId,
            ], 400);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Server Failure',
            ], 500);
        }
    }

    public function postTransaction (Request $request){
        $book_id = $request->input('book_id');
        $user_id = $request->auth->id;
        $deadline = \Carbon\Carbon::now()->toDateString();


        $validator = Validator::make($request->all(), [
            'book_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }
        $postTransaction = Transaction::create([
            'book_id' => $book_id,
            'user_id' => $user_id,
            'deadline' => $deadline
        ]);

        if ($postTransaction) {
            return response()->json([
                'success' => true,
                'message' => 'Data Transaction Created Successfully!',
                'data' => ([
                    'transaction' => $postTransaction
                ])
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Request Failed!',
            ], 400);
        }
    }

    public function updateTransaction(Request $request, $transactionId){
        // dd($transactionId);
        $updateTransaction = Transaction::findOrFail($transactionId);

        // dd($request->deadline);

        $validator = Validator::make($request->all(), [
            'deadline' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }
        if (strtolower($request->deadline) != "null") {
            return response()->json([
                'success' => false,
                'message' => "Data must nullable",
            ], 400);
        }
        try {

            $updateTransaction->deadline = null;
            $updateTransaction->save();

            $response = [
                'success' => true,
                'message' => 'Transaction Data Updated',
                'data' => ([
                    'user' => $updateTransaction
                ])
            ];
            return response()->json($response, 200);

        } catch (QueryException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 400);
        }
    }
}
