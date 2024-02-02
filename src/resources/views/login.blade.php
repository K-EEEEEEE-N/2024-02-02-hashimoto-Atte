@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}" />
@endsection

@section('content')
<div class="login__content">
    <p class="login__title">ログイン</p>
    <form action="/login" class="login__form" method="post">
        @csrf
        <div class="login__email">
            <input type="text" class="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}">
        </div>
        <div class="login__password">
            <input type="password" class="password" name="password" placeholder="パスワード" value="{{ old('password') }}">
        </div>
        <div class="login__form__submit">
            @if ($errors->has('email'))
            <div class="error">{{ $errors->first('email') }}</div>
            @endif
            <input type="submit" class="login__form__submit--input" value="ログイン">
        </div>
    </form>
    <div class="login__support">
        <p class=login__support__p>アカウントをお持ちでない方はこちらから</p>
        <a href="/register" class="login__support__a">会員登録</a>
    </div>
</div>
@endsection