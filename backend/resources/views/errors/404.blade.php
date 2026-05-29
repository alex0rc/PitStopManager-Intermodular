@extends('errors.layout')
@section('title', 'No encontrado')
@section('code', '404')
@section('icon', 'bi-signpost-split')
@section('heading', 'Página no encontrada')
@section('message', $exception->getMessage() && $exception->getMessage() !== 'Not Found'
    ? $exception->getMessage()
    : 'La página o el recurso que buscas no existe o ha sido eliminado.')
@section('actions')
    <a href="{{ url('/admin') }}" class="btn btn-primary me-2">Panel admin</a>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Inicio</a>
@endsection
