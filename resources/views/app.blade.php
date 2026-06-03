<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('saddle.brand.name', 'SaddlePHP') }}</title>
    <style>:root { --saddle-accent: {{ config('saddle.brand.accent', '#d9501f') }}; }</style>
    @foreach (\SaddlePHP\Support\AssetManifest::styles() as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach
    @if ($script = \SaddlePHP\Support\AssetManifest::script())
        <script type="module" src="{{ $script }}"></script>
    @endif
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
