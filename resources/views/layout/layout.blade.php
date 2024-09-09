<!DOCTYPE html>
<html lang="en">

<head>
    <title>Research Quran</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/spin.css') }}">

    @yield('header-scripts')
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Research Quran</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @foreach(\Config('constants.menu') as $key => $val)
                        @if(\in_array(\Auth::user()->role, $val['access_roles']))
                        <li class="nav-item">
                            <a class="nav-link {{ (\Request::segment(2)==$val['route']) ? 'text-info' : ''}}" aria-current="page" href="{{url('/dashboard/'.$val['route'])}}">{{$val['label']}}</a>
                        </li>
                        @endif
                    @endforeach
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          {{ \Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                          <a 
                            class="dropdown-item" 
                            href="{{url('/dashboard/settings')}}"
                          ><i class="bi bi-gear"></i> Settings</a>
                        @if(\Auth::check() && \Auth::user()->role =1)
                          <a 
                            class="dropdown-item" 
                            href="{{url('/dashboard/single-scholar-translation')}}"
                           ><i class="bi bi-person"></i> Single Scholar Translation</a>

                           <a 
                            class="dropdown-item" 
                            href="{{url('/dashboard/addmeaning')}}"
                           ><i class="bi bi-person"></i> Root Word Translation</a>

                           <a 
                            class="dropdown-item" 
                            href="{{url('/dashboard/get-reference-words')}}"
                           ><i class="bi bi-person"></i> Reference Word Translation</a>
                        @endif
                        <a 
                            class="dropdown-item" 
                            href="{{url('/dashboard/logout')}}"
                        ><i class="bi bi-key"></i> Logout</a>
                        </div>
                    </li>                    
                </ul>
            </div>
        </div>
    </nav>
    <section id="loading">
        <div id="loading-content"></div>
    </section>
    @yield('content')
    <div class="container">
        <!-- Modal -->
        <div class="modal fade" id="data_modal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <div id="site-url" style="display:none">{{ url('/') }}</div>
    @yield('scripts')
    <script type="text/javascript" src="{{ asset('app.js') }}"></script>
</body>

</html>
