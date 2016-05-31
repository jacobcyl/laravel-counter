<?php

namespace Jacobcyl\ViewCounter\Models;

use Illuminate\Database\Eloquent\Model;

class UserCounter extends Model
{
    protected $table = 'user_counter';
    protected $fillable = array('class_name', 'object_id', 'user_id', 'action');
}
