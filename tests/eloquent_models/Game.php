<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

// @codingStandardsIgnoreStart
class Game extends Eloquent
{
// @codingStandardsIgnoreEnd

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
