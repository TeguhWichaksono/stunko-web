{{-- halaman dashboard untuk Orang Tua --}}
@extends('adminlte::page')

@section('title', 'Dashboard Orang Tua')

@section('content_header')
    <h1>Dashboard Orang Tua</h1>
@stop

@section('content')
    <p>Selamat datang, {{ Auth::user()->name }}.</p>
@stop
