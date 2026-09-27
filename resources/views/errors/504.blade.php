@php
    $code = '504';
    $title = 'Gateway timeout';
    $tone = 'danger';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The server did not receive a timely response from an upstream server. Please try again.',
        'tone' => $tone,
    ])
@endsection