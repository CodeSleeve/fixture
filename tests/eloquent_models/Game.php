<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Game extends Eloquent
{

    public $timestamps = false;

    /**
     * A game belongs to a user.
     *
     * @return belongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }
}
