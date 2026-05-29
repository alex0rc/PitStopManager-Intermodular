@extends('errors.layout')
@section('title', 'Error del servidor')
@section('code', '500')
@section('icon', 'bi-exclamation-octagon')
@section('heading', 'Error interno')
@section('message', 'Ha ocurrido un problema en el servidor. Inténtalo de nuevo en unos minutos. Si persiste, contacta con el administrador.')
@section('actions')
    <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">Volver</a>
    <a href="{{ url('/') }}" class="btn btn-primary">Inicio</a>
@endsection
