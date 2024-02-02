@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('header__nav')
<ul class="header__nav__ul">
    <li class="header__nav__li"><a href="/">ホーム</a></li>
    <li class="header__nav__li"><a href="/attendance">日付一覧</a></li>
    <li class="header__nav__li"><a href="/users">ユーザー一覧</a></li>
    <li class="header__nav__li">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout__btn">ログアウト</button>
        </form>
    </li>
</ul>
@endsection

@section('content')
<div class="attendance__content">
    <!-- 前日・次日リンク -->
    <div class="date-navigation">
        <a href="{{ url('/attendance?selectedDate=' . \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d')) }}">&lt;</a>
        <span>{{ $currentDate }}</span>
        <a href="{{ url('/attendance?selectedDate=' . \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d')) }}">&gt;</a>
    </div>

    @foreach($attendances->groupBy('date') as $date => $dateAttendances)
    <h2>{{ $date }}</h2>
    <div class="table">
        <table class="attendance__table">
            <tr class="attendance__table__title">
                <th>名前</th>
                <th>勤務開始</th>
                <th>勤務終了</th>
                <th>休憩時間</th>
                <th>勤務時間</th>
            </tr>
            @foreach($dateAttendances as $attendance)
            <tr class="attendance__table__data">
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->begin ? \Carbon\Carbon::parse($attendance->begin)->format('H:i:s') : '' }}</td>
                <td>
                    @if ($attendance->begin && $attendance->finish)
                    <?php
                    $start = \Carbon\Carbon::parse($attendance->begin);
                    $end = \Carbon\Carbon::parse($attendance->finish);

                    // 勤務終了日が勤務開始日を跨いでいる場合、24を加算
                    if ($end->day != $start->day) {
                        $hour = $end->hour;
                        $hour = $hour + 24;
                        echo $hour . ":" . $end->format('i:s');
                    } else {
                        echo $end->format('H:i:s');
                    }
                    ?>
                    @else

                    @endif
                </td>
                <td>
                    <?php
                    $break_total = $attendance->break_total ?? '00:00:00';
                    echo \Carbon\Carbon::parse($break_total)->format('H:i:s');
                    ?>
                </td>
                <td>
                    @if ($attendance->begin && $attendance->finish)
                    {{ \Carbon\Carbon::parse($attendance->finish)->diff(\Carbon\Carbon::parse($attendance->begin))->format('%H:%I:%S') }}
                    @else
                    0:00:00
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endforeach

    <!-- ページネーションリンクを表示 -->
    {{ $attendances->links('vendor.pagination.bootstrap-4') }}
</div>
@endsection