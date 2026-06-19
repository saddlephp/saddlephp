<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var t = localStorage.getItem('saddle-theme');
            var dark = t === 'dark' || ((t === 'system' || !t) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>
    <title inertia>{{ config('saddle.brand.name', 'Saddle') }}</title>
    <link rel="icon" href="{{ asset('vendor/saddle/icon.png') }}" type="image/png">
    <style>:root { --saddle-accent: {{ app(\SaddlePHP\Saddle::class)->accent() }};@foreach (app(\SaddlePHP\Saddle::class)->theme() as $token => $value) --color-{{ $token }}: {{ $value }};@endforeach }</style>
    @foreach (\SaddlePHP\Support\AssetManifest::styles() as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach
    @foreach (app(\SaddlePHP\Saddle::class)->styles() as $style)
        <link rel="stylesheet" href="{{ $style }}">
    @endforeach
    @if ($script = \SaddlePHP\Support\AssetManifest::script())
        <script type="module" src="{{ $script }}"></script>
    @endif
    @foreach (app(\SaddlePHP\Saddle::class)->scripts() as $pluginScript)
        <script src="{{ $pluginScript }}" defer></script>
    @endforeach
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
