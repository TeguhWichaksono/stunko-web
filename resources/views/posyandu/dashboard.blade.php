{{-- halaman dashboard untuk posyandu --}}
@extends('adminlte::page')

@section('title', 'Dashboard Posyandu')

@section('content_header')
    <h1>Dashboard Posyandu</h1>
@stop

@section('content')
    <p>Selamat datang, {{ Auth::user()->name }}.</p>
@stop
