@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h1>Siparişiniz Alındı</h1>
    <p class="lead mt-3">Sipariş kodunuz: <strong>{{ $code }}</strong></p>
    <p>En kısa sürede sizinle iletişime geçilecektir.</p>

    <a href="{{ localized_route('home') }}" class="btn btn-primary mt-4">Ana Sayfa</a>
</div>
@endsection
