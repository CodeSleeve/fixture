<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Role extends Eloquent
{
    public $timestamps = false;
    protected $guarded = [];
    public static $rules = [];

    /**
     * A role belongs to many users
     *
     * @return belongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('User', 'roles_users')->withPivot('active');
    }
}
