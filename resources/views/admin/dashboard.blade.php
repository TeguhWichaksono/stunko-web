{{-- halaman dashboard untuk admin (puskesmas) --}}
@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <h1>Dashboard Admin (Puskesmas)</h1>
@stop

@section('content')
    <p>Selamat datang, {{ Auth::user()->name }}.</p>
@stop
