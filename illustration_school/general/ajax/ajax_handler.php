<?php
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    switch ($_POST['action']) {
        case 'addStock':
            require_once(getAjax('addStock'));
            $controller = new AddStock();
            $controller->addStock();
            break;
        case 'delStock':
            require_once(getAjax('delStock'));
            $controller = new DelStock();
            $controller->delStock();
            break;
        default:
            jsonResponse(['error' => '無効なアクションです']);
            break;
    }
} catch (Exception $e) {
    jsonResponse(['error' => '無効なアクションです']);
}
