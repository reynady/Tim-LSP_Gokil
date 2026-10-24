
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | ArsenalTrack</title>
</head>
<body>
    <h1>Dashboard Admin ArsenalTrack</h1>
    <p>Login sebagai administrator berhasil.</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
