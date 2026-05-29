@extends('errors.layout')
@section('title', 'Mantenimiento')
@section('code', '503')
@section('icon', 'bi-tools')
@section('heading', 'Servicio no disponible')
@section('message', 'Estamos en mantenimiento o el servidor está temporalmente saturado. Vuelve en unos minutos.')
@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">Reintentar</a>
@endsection
