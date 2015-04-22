<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

// @codingStandardsIgnoreStart
class Role extends Eloquent
{
// @codingStandardsIgnoreEnd

    public $timestamps = false;
    protected $guarded = array();
    public static $rules = array();

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
