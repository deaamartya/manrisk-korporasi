<div class="sidebar-wrapper">
	<div>
		<div class="logo-wrapper">
			<a href="{{ url('/') }}">
				<img class="img-fluid for-light logo-header" src="{{asset('assets/images/logo/logo_divisi/logo_KORPORASI.png')}}" alt="">
			</a>
			<div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
		</div>
		<div class="logo-icon-wrapper">
			<a href="{{ url('/') }}">
				<img class="img-fluid" src="{{asset('assets/images/logo/logo_divisi/logo2.png')}}" alt="">
			</a>
		</div>
		<nav class="sidebar-main">
			<div id="sidebar-menu">
				<ul class="sidebar-links" id="simple-bar">
					<li class="back-btn">
						<a href="{{ url('/') }}"><img class="img-fluid" src="{{asset('assets/images/logo/logo_divisi/logo2.png')}}" alt=""></a>
						<div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
					</li>
					<li class="sidebar-main-title">
						<div class="welcome-card @if(Auth::user()->divisi_id === 'LN' || Auth::user()->divisi_id === 'DI' || Auth::user()->divisi_id === 'KORPORASI') flex @endif">
							<div class="logo-circle text-center">
								<img src="{{ asset('assets/images/logo/logo_divisi/logo_'.Auth::user()->divisi->divisi_code.'.png') }}" height="48" />
							</div>
							<div class="welcome-text">
								<h6 class="lan-1">Welcome,</h6>
								<p class="lan-2">
									@php
										$roles = ['Risk Officer', 'Penilai', 'Penilai Korporasi', 'Risk Owner', 'Admin'];
										$columns = [Auth::user()->is_risk_officer, Auth::user()->is_penilai, Auth::user()->is_penilai_korporasi, Auth::user()->is_risk_owner, Auth::user()->is_admin];
										$valid_roles = array_keys($columns, 1);
										$c_role = count($valid_roles);
									@endphp
									@foreach($valid_roles as $i)
										@php echo $roles[$i]; @endphp
										@if ($c_role > 2 && $loop->iteration < $c_role - 1) @php echo ', ' @endphp @endif
										@if ($c_role > 2 && $loop->iteration === $c_role - 1) @php echo 'dan ' @endphp @endif
										@if ($c_role === 2 && $loop->iteration === 1) @php echo 'dan ' @endphp @endif
									@endforeach
								</p>
								<p class="lan-2">
									{{ Auth::user()->divisi->divisi }}
								</p>
							</div>
						</div>
					</li>
					@if(Auth::user()->is_risk_officer)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-officer.dashboard' ? 'active' : '' }}" href="{{route('risk-officer.dashboard')}}">
							<i data-feather="home"></i>
							<span>Dashboard</span>
						</a>
					</li>
					@elseif(Auth::user()->is_risk_owner)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-owner.dashboard' ? 'active' : '' }}" href="{{route('risk-owner.dashboard')}}">
							<i data-feather="home"></i>
							<span>Dashboard</span>
						</a>
					</li>
					@elseif(Auth::user()->is_admin)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.dashboard' ? 'active' : '' }}" href="{{route('admin.dashboard')}}">
							<i data-feather="home"></i>
							<span>Dashboard</span>
							<!-- <label class="badge badge-secondary total-notif blink_badge" style="float: right;"></label> -->
						</a>
					</li>
					@elseif(Auth::user()->is_penilai)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='penilai.dashboard' ? 'active' : '' }}" href="{{route('penilai.dashboard')}}">
							<i data-feather="home"></i>
							<span>Dashboard</span>
						</a>
					</li>
					@elseif(Auth::user()->is_penilai_korporasi)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='penilai-korporasi.dashboard' ? 'active' : '' }}" href="{{route('penilai-korporasi.dashboard')}}">
							<i data-feather="home"></i>
							<span>Dashboard</span>
						</a>
					</li>
					@endif
					@if (Auth::user()->is_risk_officer || Auth::user()->is_admin)
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><i data-feather="server"></i><span class="lan-6">Master Data</span></a>
                        <ul class="sidebar-submenu">
                            <li>
                                <a class="sidebar-link sidebar-title link-nav
                                {{ Route::currentRouteName() == 'admin.user' ? 'active' : Route::currentRouteName() == 'risk-officer.user' ? 'active' : '' }}" href="{{ Auth::user()->is_admin ? route('admin.user') : route('risk-officer.user') }}">
                                    <span>User</span>
                                </a>
                            </li>
							@if(Auth::user()->is_risk_officer)
							<li>
								<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-officer.sumber-risiko.index' ? 'active' : '' }}" href="{{route('risk-officer.sumber-risiko.index')}}">
									<span>Sumber Risiko</span>
								</a>
							</li>
                            @elseif(Auth::user()->is_admin)
                            <li>
                                <a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.divisi' ? 'active' : '' }}" href="{{route('admin.divisi')}}">

                                    <span>Divisi</span>
                                </a>
                            </li>
                            <li>
                                <a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.risiko' ? 'active' : '' }}" href="{{route('admin.risiko')}}">

                                    <span>Risiko</span>
                                </a>
                            </li>
                            <li>
                                <a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.konteks' ? 'active' : '' }}" href="{{route('admin.konteks')}}">
                                    <span>Konteks</span>
                                </a>
                            </li>
							<li>
								<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.sumber-risiko-korporasi' ? 'active' : '' }}" href="{{route('admin.sumber-risiko-korporasi')}}">

									<span>Sumber Risiko</span>
									<label class="badge badge-secondary srisiko-korporasi-notif blink_badge" style="float: right;"></label>
								</a>
							</li>
                            @endif
                        </ul>
                    </li>
					@endif
					<li class="sidebar-list">
						@if(Auth::user()->is_risk_officer && Auth::user()->is_assessment)
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-officer.pengukuran-risiko' ? 'active' : '' }}" href="{{route('risk-officer.pengukuran-risiko')}}">
							<i data-feather="edit-3"></i>
							<span>Pengukuran Risiko</span>
							<label class="badge badge-secondary pengukuran-risiko-notif blink_badge" style="float: right;"></label>
						</a>
						@elseif(Auth::user()->is_risk_owner && Auth::user()->is_assessment)
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-owner.pengukuran-risiko' ? 'active' : '' }}" href="{{route('risk-owner.pengukuran-risiko')}}">
							<i data-feather="edit-3"></i>
							<span>Pengukuran Risiko</span>
							<label class="badge badge-secondary pengukuran-risiko-notif blink_badge" style="float: right;"></label>
						</a>
						@elseif(Auth::user()->is_penilai)
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='penilai.pengukuran-risiko' ? 'active' : '' }}" href="{{route('penilai.pengukuran-risiko')}}">
							<i data-feather="edit-3"></i>
							<span>Pengukuran Risiko</span>
							<label class="badge badge-secondary pengukuran-risiko-notif blink_badge" style="float: right;"></label>
						</a>
						@endif
					</li>
					@if(Auth::user()->is_penilai_korporasi)
						<li class="sidebar-list">
							<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='penilai-korporasi.pengukuran-risiko-korporasi' ? 'active' : '' }}" href="{{route('penilai-korporasi.pengukuran-risiko-korporasi')}}">
								<i data-feather="edit"></i>
								<span>Pengukuran Risiko Korporasi</span>
								<label class="badge badge-secondary pengukuran-risiko-korporasi-notif blink_badge" style="float: right;"></label>
							</a>
						</li>
					@endif
					@if(Auth::user()->is_risk_officer)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-officer.risiko.index' ? 'active' : '' }}" href="{{route('risk-officer.risiko.index')}}">
							<i data-feather="list"></i>
							<span>Risk Register Divisi</span>
							{{-- <label class="badge badge-secondary pengukuran-risiko-korporasi-notif blink_badge" style="float: right;"></label> --}}
						</a>
					</li>
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-officer.risk-register-korporasi.index' ? 'active' : '' }}" href="{{route('risk-officer.risk-register-korporasi.index')}}">
							<i data-feather="list"></i>
							<span>Risk Register KORPORASI</span>
						</a>
					</li>
					@elseif(Auth::user()->is_risk_owner)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-owner.risiko.index' ? 'active' : '' }}" href="{{route('risk-owner.risiko.index')}}">
							<i data-feather="list"></i>
							<span>Risk Register Divisi</span>
							<label class="badge badge-secondary riskregister-divisi-notif blink_badge" style="float: right;"></label>
						</a>
					</li>
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-owner.risk-register-korporasi.index' ? 'active' : '' }}" href="{{route('risk-owner.risk-register-korporasi.index')}}">
							<i data-feather="list"></i>
							<span>Risk Register KORPORASI</span>
						</a>
					</li>
					@elseif(Auth::user()->is_admin)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.risk-register-divisi' ? 'active' : '' }}" href="{{route('admin.risk-register-divisi')}}">
							<i data-feather="sidebar"></i>
							<span>Risk Register Korp. </span>
							<label class="badge badge-secondary riskregister-divisi-notif blink_badge" style="float: right;"></label>
						</a>
					</li>
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.risk-register-korporasi.index' ? 'active' : '' }}" href="{{route('admin.risk-register-korporasi.index')}}">
							<i data-feather="sidebar"></i>
							<span>Risk Register KORPORASI</span>
						</a>
					</li>
					@elseif(Auth::user()->is_penilai_korporasi)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='penilai-korporasi.risk-register-divisi' ? 'active' : '' }}" href="{{route('penilai-korporasi.risk-register-divisi')}}">
							<i data-feather="sidebar"></i>
							<span>Risk Register Divisi</span>
						</a>
					</li>
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='penilai-korporasi.risk-register-korporasi.index' ? 'active' : '' }}" href="{{route('penilai-korporasi.risk-register-korporasi.index')}}">
							<i data-feather="sidebar"></i>
							<span>Risk Register KORPORASI</span>
						</a>
					</li>
					@endif
					@if(Auth::user()->is_risk_officer|| Auth::user()->is_admin)
						@if(Auth::user()->is_admin)
						<li class="sidebar-list">
								<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.hasil-kompilasi-risiko' ? 'active' : '' }}" href="{{route('admin.hasil-kompilasi-risiko')}}">
										<i data-feather="file"></i>
										<span>Hasil Kompilasi Risiko</span>
								</a>
						</li>
						@endif
						<li class="sidebar-list">
							<a class="sidebar-link sidebar-title link-nav
								{{ Route::currentRouteName() == 'admin.approval-hasil-mitigasi.index' ? 'active' : Route::currentRouteName() == 'risk-officer.mitigasi-plan.index' ? 'active' : '' }}" href="{{ Auth::user()->is_admin ? route('admin.approval-hasil-mitigasi.index') : route('risk-officer.mitigasi-plan.index') }}">
								<i data-feather="sidebar"></i>
								<span>Mitigasi Plan</span>
								@if(Auth::user()->is_risk_officer)
								<label class="badge badge-secondary mitigasi-risiko-notif blink_badge" style="float: right;"></label>
								@endif
								@if(Auth::user()->is_admin)
								<label class="badge badge-secondary hasil-mitigasi-notif blink_badge" style="float: right;"></label>
								@endif
							</a>
						</li>
					@endif
					@if(Auth::user()->is_admin)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='admin.mitigasi-plan.index' ? 'active' : '' }}" href="{{route('admin.mitigasi-plan.index')}}">
							<i data-feather="file-text"></i>
							{{--
							<div class="flex-row-between-center">
								<span class="me-4p">Pengajuan Mitigasi</span>
								@if ($counts > 0)
									<span class="badge rounded-pill badge-danger">{{ $counts }}</span>
								@endif
							</div>
							--}}
							<span class="me-4p">Pengajuan Mitigasi</span>
							<label class="badge badge-secondary mitigasi-korporasi-notif blink_badge" style="float: right;"></label>
						</a>
					</li>
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName() == 'admin.mitigasi-plan-korporasi.index' ? 'active' : '' }}" href="{{ route('admin.mitigasi-plan-korporasi.index') }}">
							<i data-feather="sidebar"></i>
							<span>Mitigasi Plan Korporasi</span>
							{{-- @if(Auth::user()->is_admin)
								<label class="badge badge-secondary hasil-mitigasi-korporasi-notif blink_badge" style="float: right;"></label>
							@endif --}}
						</a>
					</li>
					@endif
					@if(Auth::user()->is_risk_officer)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='risk-officer.pengajuan-mitigasi.index' ? 'active' : '' }}" href="{{route('risk-officer.pengajuan-mitigasi.index')}}">
							<i data-feather="file-text"></i>
							<span class="me-4p">Pengajuan Mitigasi</span>
						</a>
					</li>
					@endif
					@if(Auth::user()->is_admin || Auth::user()->is_risk_officer)
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='deadline-mitigasi.index' ? 'active' : '' }}" href="{{route('deadline-mitigasi.index')}}">
							<i data-feather="file-text"></i>
							<span class="me-4p">Deadline Mitigasi</span>
                            <label class="badge badge-secondary deadline-mitigasi-notif blink_badge" style="float: right;"></label>
						</a>
					</li>
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='status-proses.index' ? 'active' : '' }}" href="{{route('status-proses.index')}}">
							<i data-feather="file-text"></i>
							<span class="me-4p">Status Proses Terkini</span>
						</a>
					</li>
                    @endif
					<li class="sidebar-list">
						<a class="sidebar-link sidebar-title link-nav {{ Route::currentRouteName()=='forum' ? 'active' : '' }}" href="{{route('forum')}}">
							<i data-feather="list"></i>
							<span>Forum</span>
						</a>
					</li>
				</ul>
			</div>
		</nav>
	</div>
</div>
