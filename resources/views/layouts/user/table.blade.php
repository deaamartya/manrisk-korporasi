@extends('layouts.user.master')
@section('title')
@yield('title')
@endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/datatables.css')}}">
@endsection

@section('style')
@yield('custom-css')
@endsection

@section('breadcrumb-title')
@yield('page-title')
@endsection

@section('breadcrumb-items')
@yield('breadcrumb')
@endsection

@section('content')
@endsection

@section('script')
<script src="{{asset('assets/js/datatable/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/datatable/datatables/datatable.custom.js')}}"></script>
<script>
  $(document).ready(function() {})
</script>
@yield('custom-script')
@endsection
