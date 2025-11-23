<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <!-- Bootstrap 5 CSS (CDN) - no integrity to avoid blocking during local dev -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for admin icons (used in sidebar/header) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pb6Y6D0xKZkQ0D9X1q3QvJx5bQY0q3C6rZQ6Y6z1Dx9h6e3Q6n8vQ3q3v6k9bQ+6v3q3Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>
<body class="antialiased">
    @inertia
    <!-- Bootstrap bundle (optional for components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>