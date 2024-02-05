<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Test</title>
    @routes
</head>
<body>

    {{ $slot }}

    @vite([
        'resources/css/app.css',
    ])

</body>
</html>

