<?php
include __dir__.'/main.php';
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // handle GET request
        echo "This is GET";
        break;

    case 'POST':
        if($_POST['TYPE'] == 'login'){

        }

       
        break;

    case 'PUT':
        // handle PUT request
        break;

    case 'DELETE':
        // handle DELETE request
        break;

    default:
        echo "Unknown method";
}
