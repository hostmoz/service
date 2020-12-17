<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ isset($title) ? $title .' | '. config('app.name') :  config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/spondonit/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/spondonit/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/spondonit/css/infix.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/spondonit/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/spondonit/css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/spondonit/css/parsley.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/spondonit/css/sweetalert2.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/spondonit/css/sweet_alert2.css') }}">

    @stack('css')


</head>

<body class="admin">
    <div class="container">
        <div class="col-md-8 offset-2  mt-40">
            <div class="card" id="content">
                @section('content')

                @show
            </div>
        </div>
    </div>

    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/jquery-3.2.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/popper.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/toastr.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/parsley.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/function.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/spondonit/js/common.js') }}"></script>

    @if (session("message"))
    <script>
        toastr. {
            {
                session('status')
            }
        }('{{ session("message") }}', '{{ ucfirst(session('
            status ', '
            error ')) }}');

    </script>
    @endif
    @stack('js')

</body>

</html>
