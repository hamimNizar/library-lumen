<?php

namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        // dd($request);
        if ($request->auth->role == 'admin') {
            //  \DB::connection()->enableQueryLog();
            // $transactions = Book::all();
            // $transactions = Book::find(1)->transactions;
            $transactions = Transaction::all();

            // foreach ($transactions as $transaction) {
            //     dd($transaction->book);
            //     $out[] = $transaction->book;
            // }
            // print_r($transactions->book);
            for ($i=0; $i < $transactions->count(); $i++) {

                $book['title'] = $transactions[$i]->book->title;
                $book['author'] = $transactions[$i]->book->author;

                $trans['deadline'] = $transactions[$i]->deadline;
                $trans['created_at'] = $transactions[$i]->created_at;
                $trans['updated_at'] = $transactions[$i]->updated_at;
                // $out[$i]['deadline'] = $transactions[$i]->deadline;
                // $out[$i]['created_at'] = $transactions[$i]->created_at;
                // $out[$i]['updated_at'] = $transactions[$i]->updated_at;
                $tes[$i] = $trans;
                $tes[$i]['book'] = $book;


            }

            // dd($tes);
            if ($transactions){
                if(count($transactions) != 0){
                    return response()->json([
                        'success' => true,
                        'message' => 'List all transactions from admin',
                        'data' => [
                            'transactions' => $tes,
                        ]

                    ], 200);
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
                ], 501);
            }
        }else{
            // dd($request->auth->id);
            $transactions = Transaction::where('user_id', $request->auth->id)->first();

            $book['title'] = $transactions->book->title;
            $book['author'] = $transactions->book->author;

            $trans['deadline'] = $transactions->deadline;
            $trans['created_at'] = $transactions->created_at;
            $trans['updated_at'] = $transactions->updated_at;
            // $out['deadline'] = $transactions->deadline;
            // $out['created_at'] = $transactions->created_at;
            // $out['updated_at'] = $transactions->updated_at;
            $tes[0] = $trans;
            $tes[0]['book'] = $book;



            // dd($tes);
            if ($transactions){
                if(count($transactions->all()) != 0){
                    return response()->json([
                        'success' => true,
                        'message' => 'List transactions for user '. $request->auth->name,
                        'data' => [
                            'transactions' => $tes,
                        ]
                    ], 200);
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

    public function getTransactionById(Request $request, $transactionId){
        if($request->auth->role == 'user'){
            try{
                $transactions = Transaction::findOrFail($transactionId);
            }catch (ModelNotFoundException $error) {
                return response()->json([
                    'success' => false,
                    'message' => $error->getMessage(),
                ], 404);
            }
            // dd($request->auth->id.' '. $transactions->user_id);

             if($transactions->user_id != $request->auth->id){
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden Get Other User Transaction',
                ], 403);
            }

        }else{
            try{
                $transactions = Transaction::findOrFail($transactionId);
            }catch (ModelNotFoundException $error) {
                return response()->json([
                    'success' => false,
                    'message' => $error->getMessage(),
                ], 404);
            }
        }

        $user['name'] = $transactions->user->name;
        $user['email'] = $transactions->user->email;

        $book['title'] = $transactions->book->title;
        $book['author'] = $transactions->book->author;
        $book['description'] = $transactions->book->description;
        $book['synopsis'] = $transactions->book->synopsis;

        $trans['deadline'] = $transactions->deadline;
        $trans['created_at'] = $transactions->created_at;
        $trans['updated_at'] = $transactions->updated_at;
        // $out['deadline'] = $transactions->deadline;
        // $out['created_at'] = $transactions->created_at;
        // $out['updated_at'] = $transactions->updated_at;
        $tes = $trans;
        $tes['user'] = $user;
        $tes['book'] = $book;

        if ($transactions){
            if(!empty($transactions)){
                return response()->json([
                'success' => true,
                'message' => 'Get transaction by Id',
                'data' => ([
                    'transaction' => $tes
                ])
                ], 200);
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
            'book_id' => 'required|exists:books,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }


        try{
            $postTransaction = Transaction::create([
            'book_id' => $book_id,
            'user_id' => $user_id,
            'deadline' => $deadline
        ]);

        } catch (QueryException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 400);
        }

        // dd($postTransaction);

        if ($postTransaction) {

            $book['title'] = $postTransaction->book->title;
            $book['author'] = $postTransaction->book->author;

            $trans['deadline'] = $postTransaction->deadline;
            $trans['created_at'] = $postTransaction->created_at;
            $trans['updated_at'] = $postTransaction->updated_at;
            $tes = $trans;
            $tes['book'] = $book;

            return response()->json([
                'success' => true,
                'message' => 'Data Transaction Created Successfully!',
                'data' => [
                    'transaction' => $tes,
                ]
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
        try {
            $updateTransaction = Transaction::findOrFail($transactionId);
            $updateTransaction->deadline = null;
            $updateTransaction->save();

            $user['name'] = $updateTransaction->user->name;
            $user['email'] = $updateTransaction->user->email;

            $book['title'] = $updateTransaction->book->title;
            $book['author'] = $updateTransaction->book->author;
            $book['description'] = $updateTransaction->book->description;
            $book['synopsis'] = $updateTransaction->book->synopsis;

            $trans['deadline'] = $updateTransaction->deadline;
            $trans['created_at'] = $updateTransaction->created_at;
            $trans['updated_at'] = $updateTransaction->updated_at;

            $tes = $trans;
            $tes['user'] = $user;
            $tes['book'] = $book;

            $response = [
                'success' => true,
                'message' => 'Transaction Data Updated',
                'data' => [
                    'transaction' => $tes,
                ]
            ];
            return response()->json($response, 200);

        } catch (ModelNotFoundException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 404);
        }catch (QueryException $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 404);
        }

        if (strtolower($request->deadline) != null) {
            return response()->json([
                'success' => false,
                'message' => "Data must nullable",
            ], 403);
        }
    }
}
