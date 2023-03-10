@extends('layouts.authentication.master')
@section('title', 'Login-one')

@section('css')
@endsection

@section('style')
<style>
   .login-image {
      padding: 0;
      background-color: #185089;
   }
   .login-image img {
      object-fit: contain;
      width: 100%;
      height: 100%;
   }
   .login-card-content,
   .login-main {
      width: 100% !important;
   }
</style>
@endsection

@section('content')
<div class="container-fluid">
   <div class="row">
      {{-- <div class="col-xl-7">
         <img class="bg-img-cover bg-center" src="{{asset('assets/images/korporasi.png')}}" alt="looginpage">
      </div> --}}
      <div class="col-lg-7 login-image">
         <img src="{{asset('assets/images/korporasi.png')}}" alt="looginpage">
      </div>
      <div class="col-lg-5 p-0">
         <div class="login-card p-4">
            <div class="login-card-content">
               <div class="text-center">
                  <img src="{{asset('assets/images/logo.png')}}" alt="logo" width="60%" height="auto" class="mb-3">
                  <h4 class="mb-3" style="color: #185089;">RISK MANAGEMENT INTEGRATED SYSTEM</h4>
               </div>
               <div class="login-main">
                  <form class="theme-form">
                     <h5>Sign in to account</h5>
                     <p>Enter your username & password to login</p>
                     <div class="form-group">
                        <label class="col-form-label">Username</label>
                        <input class="form-control" type="email" required="" placeholder="Test@gmail.com">
                     </div>
                     <div class="form-group">
                        <label class="col-form-label">Password</label>
                        <input class="form-control" type="password" name="login[password]" required="" placeholder="*********">
                        <div class="show-hide mt-3"><span class="show"></span></div>
                     </div>
                     <div class="form-group mb-0">
                        <div class="checkbox p-0">
                           <input id="checkbox1" type="checkbox">
                           <label class="text-muted" for="checkbox1">Remember password</label>
                        </div>
                        <button class="btn btn-danger btn-block w-100" type="submit">Sign in</button>
                     </div>
                     <div class="social mt-4">
                        <img class="img-fluid" src="{{asset('assets/images/akorporasi.png')}}" alt="looginpage">
                     </div>
                     
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection

@section('script')
@endsection