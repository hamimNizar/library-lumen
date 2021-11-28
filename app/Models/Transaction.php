<?php

namespace App\Models;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // TODO: Insert your fillable fields
        'book_id', 'user_id', 'deadline',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // TODO: Insert your hidden fields

        'created_at', 'updated_at'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function book(){
    	return $this->belongsTo(Book::class);
    }

    public function user(){
    	return $this->belongsTo(User::class);
    }

}
