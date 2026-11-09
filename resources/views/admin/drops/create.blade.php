<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drop · Nouveau drop</title>
    <style>
        :root { --fond: #EAE6DE; --bleu: #226192; --orange: #EF8557; }
        body { margin: 0; padding: 16px; background: var(--fond); color: var(--bleu); font-family: system-ui, sans-serif; }
        main { max-width: 640px; margin: 0 auto; }
        h1 { font-size: 2rem; text-transform: uppercase; letter-spacing: .04em; }
        label { display: block; margin-top: 14px; font-weight: 700; }
        input, textarea { width: 100%; box-sizing: border-box; padding: 10px; border: 2px solid var(--bleu); background: #fff; color: var(--bleu); font: inherit; }
        .row { display: flex; gap: 8px; }
        .row > * { flex: 1; }
        button { margin-top: 20px; padding: 12px 20px; background: var(--bleu); color: var(--fond); border: 0; font: inherit; font-weight: 700; cursor: pointer; }
        .ok { background: var(--orange); color: var(--bleu); padding: 10px; margin-top: 12px; font-weight: 700; }
        .err { color: #9b1c1c; font-weight: 700; }
        .size { border-top: 2px dashed var(--orange); margin-top: 14px; padding-top: 8px; }
    </style>
</head>
<body>
<main>
    <h1>Nouveau drop</h1>

    @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul class="err">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="post" action="{{ route('admin.drops.store') }}" enctype="multipart/form-data">
        @csrf

        <label for="title">Produit</label>
        <input id="title" name="title" value="{{ old('title') }}" required maxlength="120">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required maxlength="2000">{{ old('description') }}</textarea>

        <label for="image">Photo</label>
        <input id="image" name="image" type="file" accept="image/*">

        <div class="row">
            <div>
                <label for="price">Prix (€)</label>
                <input id="price" name="price" type="number" step="0.01" min="0.01" value="{{ old('price') }}" required>
            </div>
            <div>
                <label for="reservation_minutes">Réservation (min)</label>
                <input id="reservation_minutes" name="reservation_minutes" type="number" min="1" max="60" value="{{ old('reservation_minutes', 10) }}" required>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="opens_at">Ouverture</label>
                <input id="opens_at" name="opens_at" type="datetime-local" value="{{ old('opens_at') }}" required>
            </div>
            <div>
                <label for="closes_at">Fermeture</label>
                <input id="closes_at" name="closes_at" type="datetime-local" value="{{ old('closes_at') }}" required>
            </div>
        </div>

        <div class="size">
            <label>Tailles et stock (une taille unique : « Unique »)</label>
            @for ($i = 0; $i < 4; $i++)
                <div class="row">
                    <input name="sizes[{{ $i }}][label]" placeholder="Taille" value="{{ old("sizes.$i.label") }}" maxlength="20">
                    <input name="sizes[{{ $i }}][stock]" type="number" min="1" placeholder="Stock" value="{{ old("sizes.$i.stock") }}">
                </div>
            @endfor
        </div>

        <button type="submit">Créer le drop</button>
    </form>
</main>
</body>
</html>
