@php
    $code = '422';
    $title = 'Unprocessable entity';
    $tone = 'gold';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The request could not be processed because it failed validation. Please go back, correct the input and try again.',
        'tone' => $tone,
    ])
@endsection