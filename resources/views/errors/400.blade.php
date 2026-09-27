@php
    $code = '400';
    $title = 'Bad request';
    $tone = 'gold';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The request could not be understood by the server. Please check the address and try again.',
        'tone' => $tone,
    ])
@endsection