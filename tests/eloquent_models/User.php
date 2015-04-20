<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent
{

    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes on this model that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * A user belongs to many roles
     *
     * @return belongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('Role', 'roles_users')->withPivot('active');
    }

    /**
     * A user has many games.
     *
     * @return hasMany
     */
    public function games()
    {
        return $this->hasMany('Game');
    }
}
