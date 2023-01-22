<div class="page-header">
  <div class="header-wrapper row m-0">
    <form class="form-inline search-full col" action="#" method="get">
      <div class="mb-3 w-100">
        <div class="Typeahead Typeahead--twitterUsers">
          <div class="u-posRelative">
            <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search Cuba .." name="q" title="" autofocus>
            <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div>
            <i class="close-search" data-feather="x"></i>
          </div>
          <div class="Typeahead-menu"></div>
        </div>
      </div>
    </form>
    <div class="header-logo-wrapper col-4 p-0">
      <div class="logo-wrapper"><a href="{{ url('/') }}"><img class="img-fluid" src="{{asset('assets/images/logo/logo_company/logo_INHAN.png')}}" alt=""></a></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
    </div>
    <div class="nav-right col-8 pull-right right-header p-0">
      <ul class="nav-menus" style="margin-right: 0px">
        <li class="onhover-dropdown">
          <div class="notification-box">
            <i data-feather="bell"></i>
            <span class="badge rounded-pill badge-secondary total-notif"></span>
          </div>
          <ul class="notification-dropdown onhover-show-div body-notif">
            <li>
              <i data-feather="bell"></i>
              <h6 class="f-18 mb-0">Notitications</h6>
            </li>

            {{-- <li><a class="btn btn-primary" href="#">Check all notification</a></li> --}}
          </ul>
        </li>
        <li class="profile-nav onhover-dropdown p-0 me-0">
          <div class="media profile-media">
            <img class="b-r-10" src="{{asset('assets/images/dashboard/profile.jpg')}}" alt="">
            <div class="media-body">
              <p class="mb-0 font-roboto">
                {{ Auth::user()->perusahaan->instansi }}
                <i class="middle fa fa-angle-down"></i>
              </p>
            </div>
          </div>
          <ul class="profile-dropdown onhover-show-div">
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-link-custom text-reset p-0" type="submit">
                  <i data-feather="log-out"></i>
                  Log out
                </button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
    <script class="result-template" type="text/x-handlebars-template">
      <div class="ProfileCard u-cf">
      <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
      <div class="ProfileCard-details">
      <div class="ProfileCard-realName">@{{name}}</div>
      </div>
      </div>
    </script>
    <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
  </div>
</div>
