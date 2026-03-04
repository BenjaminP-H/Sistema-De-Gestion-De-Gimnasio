<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Drago Gym</title>

    <base href="/gym/">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="<?= isset($page_class) ? $page_class : '' ?>">