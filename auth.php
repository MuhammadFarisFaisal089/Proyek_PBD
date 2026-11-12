<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function require_login()
{
    if (empty($_SESSION['status_login']) || empty($_SESSION['iduser']) || empty($_SESSION['idrole'])) {
        header('Location: login.php');
        exit;
    }
}

function require_roles($roles)
{
    require_login();

    if (!in_array((int) $_SESSION['idrole'], $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf()
{
    $token = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if ($token === '' || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}

function execute_procedure($connection, $sql, $types, &$params)
{
    $statement = $connection->prepare($sql);
    $arguments = array_merge(array($types), $params);
    $references = array();

    foreach ($arguments as $key => &$argument) {
        $references[$key] = &$argument;
    }

    try {
        call_user_func_array(array($statement, 'bind_param'), $references);
        $statement->execute();

        do {
            if ($statement->field_count > 0) {
                $statement->store_result();
                $statement->free_result();
            }
        } while ($statement->more_results() && $statement->next_result());
    } finally {
        $statement->close();
    }
}
