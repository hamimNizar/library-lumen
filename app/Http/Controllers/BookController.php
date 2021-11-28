<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
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
    
    public function index()
    {
        $books = Book::all();
        // var_dump(count($books));die;
        if ($books) {
            if (count($books) != 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'List all Books',
                    'data' => ([
                        'books' => $books
                    ])
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No books',
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Server Failure',
            ], 500);
        }
    }

    public function getBookById(Request $request, $bookId)
    {
        $book = Book::find($bookId);
        if ($book) {
            return response()->json([
                'success' => true,
                'message' => 'Get Book by Id',
                'data' => ([
                    'book' => $book
                ])
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'there is no book with id = ' . $bookId,
            ], 404);
        }
    }

    public function postBook(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'author' => 'required',
            'year' => 'required',
            'synopsis' => 'required',
            'stock' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $author = $request->input('author');
        $year = $request->input('year');
        $synopsis = $request->input('synopsis');
        $stock = $request->input('stock');

        $postBook = Book::create([
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'year' => $year,
            'synopsis' => $synopsis,
            'stock' => $stock,
        ]);

        if ($postBook) {
            return response()->json([
                'success' => true,
                'message' => 'Data Book Created Successfully!',
                'data' => ([
                    'book' => $postBook
                ])
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Request Failed!',
            ], 400);
        }
    }

    public function updateBook(Request $request, $bookId)
    {
        // dd($request->auth);
        if ($request->auth->role == 'admin') {
            $updateBook = Book::find($bookId);

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'synopsis' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors(),
                ], 400);
            }

            if ($updateBook) {
                try {
                    $updateBook->update($request->all());
                    $response = [
                        'success' => true,
                        'message' => 'Book Data Updated',
                        'data' => ([
                            'book' => $updateBook
                        ])
                    ];
                    return response()->json($response, 200);
                } catch (QueryException $error) {
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal" . $error->errorInfo,
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Request Failed',
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden Access, You are not authorized',
            ], 404);
        }
    }

    public function deleteBook(Request $request, $bookId)
    {
        if ($request->auth->role == 'admin') {
            $deleteBook = Book::find($bookId);
            if ($deleteBook) {
                try {
                    $deleteBook->delete();
                    $response = [
                        'success' => true,
                        'message' => 'Book Data Deleted',
                    ];
                    return response()->json($response, 200);
                } catch (QueryException $error) {
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal" . $error->errorInfo,
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Request Failed',
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden Access, You are not authorized',
            ], 404);
        }
    }
}