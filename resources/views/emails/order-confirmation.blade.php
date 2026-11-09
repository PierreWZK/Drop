<p>Merci pour ta commande.</p>

<p><strong>{{ $order->drop->title }}</strong><br>
Montant : {{ number_format($order->amount_cents / 100, 2, ',', ' ') }} €<br>
Payée le : {{ $order->paid_at->format('d/m/Y H:i') }}</p>

@if ($order->shipping_address)
<p>Livraison :<br>{!! nl2br(e($order->shipping_address)) !!}</p>
@endif

<p>Référence : #{{ $order->id }}</p>
<p>Drop · un produit, un jour, pas un de plus</p>
