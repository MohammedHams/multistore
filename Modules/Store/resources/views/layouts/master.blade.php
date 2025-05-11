@extends('layouts.app')

@section('title', $title ?? 'Store Management - Multistore Admin')

@section('page_title', $pageTitle ?? 'Store Management')

@section('content')
    @yield('store_content')
@endsection
