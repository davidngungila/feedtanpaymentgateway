@php
    $code = '502';
    $title = 'Bad gateway';
    $tone = 'danger';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The server received an invalid response from an upstream server. Please try again.',
        'tone' => $tone,
    ])
@endsection