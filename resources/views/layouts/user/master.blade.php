<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cuba admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Cuba admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{asset('assets/images/logo/logo_divisi/logo2.png')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/logo/logo_divisi/logo2.png')}}" type="image/x-icon">
    <title>Manrisk - Korporasi</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    @include('layouts.user.css')
    @yield('style')
  </head>
  <body>
    <div class="loader-wrapper">
      <div class="loader-index"><span></span></div>
      <svg>
        <defs></defs>
        <filter id="goo">
          <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
          <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
        </filter>
      </svg>
    </div>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <!-- Page Header Start-->
      @include('layouts.user.header')
      <!-- Page Header Ends  -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        @include('layouts.user.sidebar')
        <!-- Page Sidebar Ends-->
        <div class="page-body" style="background-image: url('{{ asset('assets/images/bg-abs.png') }}'); background-size: 100% 100%; background-repeat: repeat;">
          <div class="container-fluid">
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  @yield('breadcrumb-title')
                </div>
                <div class="col-6">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}"> <i data-feather="home"></i></a></li>
                    @yield('breadcrumb-items')
                  </ol>
                </div>
              </div>
            </div>
            @if(Session::has('created-alert') || Session::has('updated-alert') || Session::has('deleted-alert') || Session::has('error-alert'))
            <div class="alert
              @if(Session::has('created-alert') || Session::has('updated-alert'))
              alert-success
              @elseif(Session::has('deleted-alert'))
              alert-info
              @elseif(Session::has('error-alert'))
              alert-danger
              @endif">
              @if(Session::has('created-alert'))
              {{ Session::get('created-alert') }}
              @elseif(Session::has('updated-alert'))
              {{ Session::get('updated-alert') }}
              @elseif(Session::has('deleted-alert'))
              {{ Session::get('deleted-alert') }}
              @elseif(Session::has('error-alert'))
              {{ Session::get('error-alert') }}
              @endif
            </div>
            @endif
            @if(Session::has('deadline-mitigasi'))
                <a href="{{ url('deadline-mitigasi') }}"><div class="alert alert-info">
                    Terdapat mitigasi yang telah jatuh tempo sebanyak <span style="font-weight: bold">{{ Session::get('deadline-mitigasi') }}</span>. Mohon Segera Dicek!
                </div></a>
            @endif
          </div>
          <!-- Container-fluid starts-->
          @yield('content')
          <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
        @include('layouts.user.footer')
      </div>
    </div>
    @if(Session::has('success-swal') || Session::has('warning-swal') || Session::has('error-swal'))
    <script src="{{asset('assets/js/sweet-alert/sweetalert.min.js')}}"></script>
      @if(Session::has('success-swal'))
      <script type="text/javascript">
        console.log('success')
        swal("Berhasil!", "{{ Session::get('success-swal') }}", "success");
      </script>
      @elseif(Session::has('warning-swal'))
      <script type="text/javascript">
        console.log('warning')
        swal("Warning!", "{{ Session::get('warning-swal') }}", "warning");
      </script>
      @elseif(Session::has('error-swal'))
      <script type="text/javascript">
        console.log('error')
        swal("Oops!", "{{ Session::get('error-swal') }}", "error");
      </script>
      @endif
    @endif
    <script>
        const APP_URL = {!! json_encode(url('/')) !!}
    </script>
    <!-- latest jquery-->
    @include('layouts.user.script')
    <!-- Plugin used-->
    <script type="text/javascript">
      if ($(".page-wrapper").hasClass("horizontal-wrapper")) {
        $(".according-menu.other" ).css( "display", "none" );
        $(".sidebar-submenu" ).css( "display", "block" );
      }
    </script>
  </body>
</html>
