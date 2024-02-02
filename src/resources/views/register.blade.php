@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}" />
@endsection

@section('content')
<div class="register__content">
    <p class="register__title">会員登録</p>
    <form action="{{ route('register') }}" class="register__form" method="post">
        @csrf
        <div class="register__name">
            <input type="text" class="name" name="name" placeholder="名前" value="{{ old('name') }}">
            @error('name')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="register__email">
            <input type="text" class="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}">
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="register__password">
            <input type="password" class="password" name="password" placeholder="パスワード" value="{{ old('password') }}">
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <div class="register__confirmation">
            <input type="password" class="confirmation" name="password_confirmation" placeholder="確認用パスワード" value="{{ old('password_confirmation') }}">
        </div>
        <div class="register__form__submit">
            <input type="submit" value="会員登録">
        </div>
    </form>
    <div class="register__support">
        <p class=register__support__p>アカウントをお持ちの方はこちらから</p>
        <a href="/login" class="register__support__a">ログイン</a>
    </div>
</div>
@endsection