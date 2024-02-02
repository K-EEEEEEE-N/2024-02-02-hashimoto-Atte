<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>attendance</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>

<body>
    <header class=header__inner>
        <div class="header__title">
            <p class="header__title--p">Atte</p>
        </div>
        <div class="header__nav">
            @yield('header__nav')
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        <div class="footer__inner">
            <small class="footer__text">
                Atte, inc.
            </small>
        </div>
    </footer>
</body>