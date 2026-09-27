@php
    $code = '500';
    $title = 'Something went wrong';
    $tone = 'danger';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'An unexpected error occurred on our servers. Please try again in a moment.',
        'tone' => $tone,
    ])
@endsection