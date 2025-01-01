<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural of the model name
    protected $table = 'user_information';

    // Specify the fillable fields (if needed for mass assignment)
    protected $fillable = ['name', 'email', 'phone', 'address'];
}
