{{-- memakai tampilan AdminLTE, tidak butuh Node/Vite --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>Selamat datang, {{ Auth::user()->name }}.</p>
@stop
