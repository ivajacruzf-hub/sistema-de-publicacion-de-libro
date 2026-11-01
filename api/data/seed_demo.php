<?php
require_once __DIR__ . '/../src/config/conexionDB.php';
require_once __DIR__ . '/../src/models/Users.php';
require_once __DIR__ . '/../src/models/ResourceModel.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    $users = Users::all();
    $demoUserExists = false;
    foreach ($users as $user) {
        if ($user['username'] === 'usuario_demo') {
            $demoUserExists = true;
            break;
        }
    }

    if ($demoUserExists) {
        echo "El usuario usuario_demo ya existe; no se modificó.\n";
    } else {
        $password = bin2hex(random_bytes(8));
        Users::add([
            'username' => 'usuario_demo',
            'password' => $password,
            'rol' => 'Lector',
            'activo' => 1
        ]);
        echo "Usuario creado: usuario_demo\nContraseña temporal: $password\n";
        echo "Guarda esta contraseña ahora; no se puede volver a mostrar.\n";
    }

    $authors = ResourceModel::all('autor');
    $author = null;
    foreach ($authors as $record) {
        if ($record['CI'] === 'DEMO-AUTOR-001') {
            $author = $record;
            break;
        }
    }
    if ($author === null) {
        ResourceModel::create('autor', [
            'CI' => 'DEMO-AUTOR-001',
            'nombre' => 'Alex',
            'apellidos' => 'Rivera',
            'biografia' => 'Autor de ejemplo para probar el sistema.'
        ]);
        $authors = ResourceModel::all('autor');
        foreach ($authors as $record) {
            if ($record['CI'] === 'DEMO-AUTOR-001') {
                $author = $record;
                break;
            }
        }
        echo "Autor de ejemplo creado.\n";
    } else {
        echo "El autor de ejemplo ya existe; no se modificó.\n";
    }

    $readers = ResourceModel::all('lector');
    $readerExists = false;
    foreach ($readers as $record) {
        if ($record['CI'] === 'DEMO-LECTOR-001') {
            $readerExists = true;
            break;
        }
    }
    if (!$readerExists) {
        ResourceModel::create('lector', [
            'CI' => 'DEMO-LECTOR-001',
            'nombre' => 'Sam',
            'apellidos' => 'López',
            'email' => 'lector.demo@example.com'
        ]);
        echo "Lector de ejemplo creado.\n";
    } else {
        echo "El lector de ejemplo ya existe; no se modificó.\n";
    }

    $books = ResourceModel::all('libro');
    $book = null;
    foreach ($books as $record) {
        if ($record['titulo'] === 'El jardín de las historias') {
            $book = $record;
            break;
        }
    }
    if ($book === null) {
        $bookId = ResourceModel::create('libro', [
            'isbn' => null,
            'titulo' => 'El jardín de las historias',
            'sinopsis' => 'Libro de ejemplo para probar el sistema de publicaciones.',
            'estado_publicacion' => 'En Redacción',
            'fecha_publicacion' => null
        ]);
        echo "Libro de ejemplo creado.\n";
    } else {
        $bookId = $book['id'];
        echo "El libro de ejemplo ya existe; no se modificó.\n";
    }

    if ($author !== null) {
        $relationship = ConexionPDO::query(
            'SELECT 1 FROM autor_libro WHERE cod_libro = :book AND cod_autor = :author LIMIT 1',
            [':book' => $bookId, ':author' => $author['id']]
        );
        if ($relationship === []) {
            ConexionPDO::execute(
                "INSERT INTO autor_libro (cod_libro, cod_autor, tipo_participacion)
                 VALUES (:book, :author, 'Autor Principal')",
                [':book' => $bookId, ':author' => $author['id']]
            );
            echo "Autor vinculado al libro de ejemplo.\n";
        }
    }
} catch (Throwable $error) {
    fwrite(STDERR, "No se pudieron cargar los datos de ejemplo: {$error->getMessage()}\n");
    exit(1);
}
