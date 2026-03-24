@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
        <div class="inline-block">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-amber-600"></div>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mt-6 mb-2">Memproses Pembayaran</h1>
        <p class="text-gray-600">Silakan tunggu sebentar...</p>
    </div>
</div>

<!-- Auto-submit POST form (hidden) -->
<form id="processPaymentForm" action="{{ route('payment.process', ['order' => $order->id]) }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="return_to" value="{{ $returnTo ?? route('checkout.show') }}">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form to POST endpoint
    document.getElementById('processPaymentForm').submit();
});
</script>
@endsection
