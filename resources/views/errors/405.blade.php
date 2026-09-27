@php
    $code = '405';
    $title = 'Method not allowed';
    $tone = 'gold';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'This page does not support the method you used to access it.',
        'tone' => $tone,
    ])
@endsection