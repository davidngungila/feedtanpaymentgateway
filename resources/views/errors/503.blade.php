@php
    $code = '503';
    $title = 'Service unavailable';
    $tone = 'danger';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The service is temporarily unavailable, usually for maintenance. Please try again shortly.',
        'tone' => $tone,
    ])
@endsection