<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('rodeo.brand.name', 'RodeoPHP') }}</title>
    <style>:root { --rodeo-accent: {{ config('rodeo.brand.accent', '#d9501f') }}; }</style>
    @foreach (\RodeoPHP\Support\AssetManifest::styles() as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach
    @if ($script = \RodeoPHP\Support\AssetManifest::script())
        <script type="module" src="{{ $script }}"></script>
    @endif
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
