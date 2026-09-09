<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: system-ui, sans-serif; color: #334155; background: #f8fafc; }
        p { margin: 0; opacity: .75; }
    </style>
</head>
<body>
    <p>Signing you in…</p>
    <script>
        (function () {
            var token = @json($token);
            var redirectTo = @json($redirect);
            try {
                window.localStorage.setItem('cv.auth.token', token);
            } catch (e) {}
            window.location.replace(redirectTo);
        })();
    </script>
</body>
</html>
