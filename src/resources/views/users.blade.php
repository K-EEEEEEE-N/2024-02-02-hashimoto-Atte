@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/users.css') }}" />
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
<div class="users__content">
    <table class="users__table">
        <tr class="users__table__title">
            <th>名前</th>
            <th>メールアドレス</th>
        </tr>
        @foreach($users as $user)
        <tr class="users__table__data">
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endsection