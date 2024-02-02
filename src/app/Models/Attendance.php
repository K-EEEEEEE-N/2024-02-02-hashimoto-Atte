<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'begin',
        'finish',
        'break_begin',
        'break_finish',
        'break_total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 勤務時間を計算するメソッド
    public function calculateWorkTime()
    {
        if ($this->begin && $this->finish) {
            $begin = strtotime($this->begin);
            $finish = strtotime($this->finish);

            $workTimeSeconds = $finish - $begin;

            return gmdate('H:i:s', $workTimeSeconds);
        }

        return null;
    }
}
