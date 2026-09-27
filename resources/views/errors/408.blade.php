@php
    $code = '408';
    $title = 'Request timeout';
    $tone = 'gold';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The server timed out waiting for your request. Please try again.',
        'tone' => $tone,
    ])
@endsection