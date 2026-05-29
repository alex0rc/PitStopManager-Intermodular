@extends('errors.layout')
@section('title', 'Acceso denegado')
@section('code', '403')
@section('icon', 'bi-shield-lock')
@section('heading', 'Acceso denegado')
@section('message', 'No tienes permiso para acceder a esta sección. Inicia sesión con una cuenta autorizada.')
@section('actions')
    <a href="{{ route('admin.login') }}" class="btn btn-primary me-2">Iniciar sesión</a>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Inicio</a>
@endsection
