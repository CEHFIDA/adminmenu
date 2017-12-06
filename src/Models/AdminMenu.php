<?php

namespace Selfreliance\adminmenu\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
	public $timestamps = false;
	
    protected $table = 'admin__menu';

    protected $fillable = [
    	'title', 'icon', 'package', 'parent', 'sort'
    ];

    public function normalSpace($value)
    {
        return str_replace('&nbsp;', ' ', htmlentities($value, null, 'utf-8'));
    }
}
