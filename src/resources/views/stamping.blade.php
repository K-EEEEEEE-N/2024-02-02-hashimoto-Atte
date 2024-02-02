@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamping.css') }}" />
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
<div class="attendance__btn">
    <p class="greeting">{{ Auth::user()->name }}さんお疲れ様です！</p>
    <div class="work">
        <form action="{{ route('beginWork') }}" method="post">
            @csrf
            <button type="submit" class="begin" name="button_pressed" value="begin" {{ !is_null($search_item['begin']) && is_null($search_item['finish']) ? 'disabled' : '' }}>勤務開始</button>
        </form>
        <form action="{{ route('endWork') }}" method="post">
            @csrf
            <button type="submit" class="finish" name="button_pressed" value="finish" {{ is_null($search_item['begin']) || !is_null($search_item['finish']) || !is_null($search_item['break_begin']) && is_null($search_item['break_finish']) ? 'disabled' : '' }}>勤務終了</button>
        </form>
    </div>
    <div class="break">
        <form action="{{ route('beginBreak') }}" method="post">
            @csrf
            <input type="hidden" name="break_begin" value="{{ optional($search_item['break_begin'])->format('Y-m-d H:i:s') }}">
            <button type="submit" class="break__begin" name="button_pressed" value="break_begin" {{ is_null($search_item['begin']) || !is_null($search_item['finish']) || !is_null($search_item['break_begin']) && is_null($search_item['break_finish']) ? 'disabled' : '' }}>休憩開始</button>
        </form>
        <form action="{{ route('endBreak') }}" method="post">
            @csrf
            <input type="hidden" name="break_finish" value="{{ optional($search_item['break_finish'])->format('Y-m-d H:i:s') }}">
            <button type="submit" class="break__finish" name="button_pressed" value="break_finish" {{ is_null($search_item['break_begin']) || !is_null($search_item['finish']) || !is_null($search_item['break_begin']) && !is_null($search_item['break_finish']) ? 'disabled' : '' }}>休憩終了</button>
        </form>
    </div>
</div>
@endsection