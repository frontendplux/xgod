<?php
// ==========================================================
// GLOBAL CATEGORY LIST
// ==========================================================
$SHOPLENCA_CATEGORIES = [
    ["label" => "Appliances", "img" => "appliances.webp"],
    ["label" => "Phones & Tablets", "img" => "phone&tablet.jpeg"],
    ["label" => "Health & Beauty", "img" => "health&beauty.webp"],
    ["label" => "Home & Office", "img" => "Home&Office.webp"],
    ["label" => "Electronics", "img" => "electronic-sales.jpeg"],
    ["label" => "Fashion", "img" => "fashion.jpeg"],
    ["label" => "Supermarket", "img" => "Supermarket.jpg"],
    ["label" => "Computing", "img" => "Computing.jpeg"],
    ["label" => "Baby Products", "img" => "Baby-Products.jpeg"],
    ["label" => "Gaming", "img" => "Gaming.jpeg"],
    ["label" => "Musical Instruments", "img" => "Musical-Instruments.jpeg"],
    ["label" => "Other categories", "img" => "Other-categories.jpg"]
];

// ==========================================================
// DATABASE CONFIGURATION
// ==========================================================
if (!defined('DB_NAME')) define('DB_NAME', 'shoplenca');
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
// ===============================================
function paymentCheckout($conn){
    $authUser = userauth($conn);
    if($authUser["status"] !== true) return;

    $cartItems = fetchCartItems($conn);

    $quantity = 0;
    $err = 0;
    $total = count($cartItems);

    // Prepare statements once
    $selectStmt = $conn->prepare("
        SELECT p.*, p.id as product_id
        FROM products p
        LEFT JOIN productCart pc ON pc.p_id = p.id
        WHERE p.id = ? AND pc.p_id IS NULL
    ");

    $insertStmt = $conn->prepare("
        INSERT INTO productCart(user_id, p_id, p_location, returnable) 
        VALUES (?, ?, ?, FALSE)
    ");

    foreach ($cartItems as $item) {
        $productId = $item['id'];

        // Check if product is not already in productCart
        $selectStmt->bind_param("i", $productId);
        if ($selectStmt->execute()) {
            $result = $selectStmt->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Insert into productCart
                $insertStmt->bind_param(
                    "iis", 
                    $authUser["id"], 
                    $row['product_id'], 
                    $_SESSION['shipping_address']
                );

                if ($insertStmt->execute()) {
                    $quantity++;
                } else {
                    $err++;
                }

            } else {
                $err++;
            }
        } else {
            $err++;
        }
    }

    // Close statements
    $selectStmt->close();
    $insertStmt->close();

    return [$total, $quantity, $err];
}

// =================================================
function currency_symbol($user_country){
   switch ($user_country){
    case 'NGN':
        return '&#8358;'; // ₦
        break;
    case 'GHS':
        return 'GH&#162;'; // GH₵ — Ghana cedi
        break;
    case 'ZAR':
        return 'R'; // South African rand
        break;
    case 'KES':
        return 'KSh'; // Kenyan shilling
        break;
    default:
        return '&#36;'; // $
        break;
   }
}
function getCountryAndCurrency() {
    $ip = $_SERVER['REMOTE_ADDR']; // user's IP address

    // Use IP-API to get country info
    $response = @file_get_contents("http://ip-api.com/json/{$ip}");
    if ($response === FALSE) {
        return ['country' => 'Unknown', 'currency' => 'USD'];
    }

    $data = json_decode($response);

    if ($data && $data->status === 'success') {
        $country = strtolower($data->country);

        // Set currency based on country
        switch ($country) {
            case 'nigeria':
                $currency = 'NGN';
                break;
                
            case 'ghana':
                $currency = 'GHS';
                break;

            case 'south africa':
                $currency = 'ZAR'; // South African Rand
                break;

            case 'Kenya':
                $currency = 'KES'; // South African Rand
                break;
            default:
                $currency = 'USD';
                break;
        }

        return [
            'country' => ucfirst($data->country),
            'currency' => $currency
        ];

    } else {
        return ['country' => 'Unknown', 'currency' => 'USD'];
    }
}
// ==========================================================
// get product float 1
// ==========================================================
// function getProductsRows($conn, $data) {
//     $data = json_decode($data);

//     // Set defaults if not provided
//     $category = $data->category ?? '';
//     $maxPrice = $data->maxPrice ?? 1000000;
//     $limit = isset($data->limit) ? (int)$data->limit : 0;

//     // ✅ Prepare SQL statement
//     $sql = "SELECT * FROM products p
//             WHERE p.p_category = ? 
//             AND p.p_selling_price <= ?
//             AND p.id is NOT IN (
//                 SELECT p_id FROM productCart WHERE  p_id != p.pid
//             )
//             ORDER BY p.id DESC 
//             LIMIT ?, 20";

//     $smt = $conn->prepare($sql);
//     if (!$smt) {
//         die("Prepare failed: " . $conn->error);
//     }

//     // ✅ Bind parameters: s = string, d = double, i = integer
//     $smt->bind_param("sdi", $category, $maxPrice, $limit);

//     // ✅ Execute
//     $smt->execute();

//     // ✅ Get results
//     $result = $smt->get_result();
//     $products = [];
//     while ($row = $result->fetch_assoc()) {
//         $products[] = $row;
//     }

//     $smt->close();
//     return $products;
// }
// session_destroy();
function getProductsRows($conn, $data) {
    $data = json_decode($data);

    // Set defaults if not provided
    $category = $data->category ?? '';
    $maxPrice = $data->maxPrice ?? 1000000;
    $country = $data->country;
    $offset   = isset($data->limit) ? (int)$data->limit : 0; // pagination offset

 $sql = "
    SELECT 
        p.*, 
        p.id AS product_id,
        m.id AS member_id,
        m.user AS sellername,
        m.email,
        m.profile AS member_profile,
        m.whatsapp
    FROM products p
    JOIN member m ON m.id = p.user_id
    WHERE p.p_category = ?
      AND p.p_selling_price <= ?
      AND p.p_currency = ?
      AND p.id NOT IN (SELECT p_id FROM productCart)
    ORDER BY p.id DESC
    LIMIT ?, 20
";


    $smt = $conn->prepare($sql);
    if (!$smt) {
        die('Prepare failed: ' . $conn->error);
    }

    // ✅ Bind parameters: category (string), maxPrice (double/float), offset (int)
    $smt->bind_param("sdsi", $category, $maxPrice,$country, $offset);

    // ✅ Execute
    $smt->execute();

    // ✅ Fetch results
    $result = $smt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $smt->close();
    return $products;
}


// ========================================================
// MEMBER CHECKING STEADY
// =========================================================

function userauth($conn) {
    $email = isset($_SESSION['user']) ? mysqli_real_escape_string($conn, trim($_SESSION['user'])) : "";
    $_id   = isset($_SESSION['_id']) ? mysqli_real_escape_string($conn, trim($_SESSION['_id'])) : "";

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $query = $conn->prepare("
            SELECT * FROM member 
            WHERE uids = ? 
              AND email = ? 
              AND (status != 'blocked' AND status != 'pending') 
            LIMIT 1
        ");
        $query->bind_param('ss', $_id, $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            $emailUsername = explode('@', $row['email'])[0];


            return [
                'status'       => true,
                'id'           => $row['id'],
                'email'        => $row['email'],
                'username'     => substr($row['user'],0,5),
                '_id'          => $row['uids'],
                "message"      => "session active",
                "user_status"  => $row['status'],
                "profile"      => json_decode($row['profile'], true) ?? [],
                "wallet"       => json_decode($row['wallet'], true) ?? []
            ];
        } else {
            return [
                'status'  => false,
                "message" => "session already expired, try again",
            ];
        }
    } else {
        return [
            'status'  => false,
            "message" => "session already expired, try again",
        ];
    }
}



// ==========================================================
// FETCH GLOBAL COMPANY DATA (Available on all pages)
// ==========================================================
$companydata = $conn->query('SELECT * FROM companyData LIMIT 1');
$infos = $companydata ? $companydata->fetch_assoc() : [];

// ==========================================================
// HELPER: AUTHENTICATION
// ==========================================================
function authicate($conn){
    $user  = $_SESSION['user']  ?? '';
    $email = $_SESSION['email'] ?? '';
    $uids  = $_SESSION['_id']   ?? '';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $smt = $conn->prepare("
            SELECT * FROM member 
            WHERE uids = ? AND email = ? 
            AND (status != 'blocked' AND status != 'pending') 
            LIMIT 1
        ");
        $smt->bind_param('ss', $uids, $email);
        $smt->execute();
        $result = $smt->get_result();

        if ($result && $result->num_rows) {
            $row = $result->fetch_assoc();
            return [
                'status' => true,
                'profile' => json_decode($row['profile']),
                'message' => 'Account is active'
            ];
        } else {
            return [
                'status' => false,
                'profile' => 'Account not found or inactive'
            ];
        }
    } else {
        return [
            'status' => false,
            'profile' => 'Invalid email address'
        ];
    }
}



// ==========================================================
// MY AVAILABLE CART
// ==========================================================
function  myCart($conn,$limit = 0){
    if(authicate($conn)['status'] === true){
        $user_id =authicate($conn)['id'] ?? 0;
        $smt=$conn->prepare("select * from productCart where user_id=? order by id desc limit  $limit,10");
        $smt->bind_param('i',$user_id);
        $smt->execute();
        $result=$smt->get_result();
        return $result->fetch_assoc();
    }
    else return [];
}



// =========================================================
// TRANSACTIONS
// ==========================================================

function transact($conn,$data) {
$offset = $data['limit'] ?? 0;
$limit = 40;

$uid = trim($_SESSION['_id']) ?? '';
$email = trim($_SESSION['user']) ?? '';

if (empty($uid) || empty($email)) {
return [];
}

// Safe to inject integers after casting
$sql = "
SELECT 
    t.id AS tid, 
    t.user_id, 
    t.code, 
    t.datas, 
    t.type,
    t.status AS tast
FROM member m
JOIN transactions t ON t.user_id = m.id
WHERE m.uids = ? AND m.email = ?
ORDER BY t.id DESC
LIMIT $offset, $limit
";

$smt = $conn->prepare($sql);
if ($smt === false) {
return [];

}

$smt->bind_param('ss', $uid, $email);
$smt->execute();

$result = $smt->get_result();
$res = [];

while ($row = $result->fetch_assoc()) {
$res[] = [
    "id" => $row['tid'],
    "data" => json_decode($row['datas'], true),
    "status" => $row['tast'],
    "type" => $row['type'],
];
}
return $res;
}


// ----------------------------------------------------

function renderRandomCategorySlider($conn, $SHOPLENCA_CATEGORIES) {
    // Pick a random category label
    $randomLabel = $SHOPLENCA_CATEGORIES[array_rand($SHOPLENCA_CATEGORIES)]['label'];

    // Fetch products for that random category
    $products = getProductsRows($conn, json_encode([
        "category" => $randomLabel,
        "limit" => 0,
        "maxPrice" => 1000000,
        "country" => currency_symbol(getCountryAndCurrency()['currency'])
    ]));

    // If no products found, don’t show anything
    if (empty($products)) return;

    ?>
    <div class="bg-white container swiper productSwiper p-0 my-3">
        <div class="d-flex fs-6 px-3 py-2 my-2 mt-0 justify-content-between align-items-center">
            <h2 class="text-capitalize m-0 fs-5"><?= htmlspecialchars($randomLabel) ?></h2>
            <a href="/category?category=<?= urlencode($randomLabel) ?>" class="text-decoration-none">See all</a>
        </div>

        <div class="swiper-wrapper p-2">
            <?php foreach ($products as $p): 
                $images = json_decode($p['p_image'], true) ?? [];
                $firstImage = !empty($images) ? $images[0] : '/placeholder.jpg';
                $price = (float) $p['p_main_price'];
                $discount = (float) $p['p_selling_price'];
                $discountPercent = $price > 0 ? round((1 - $discount / $price) * 100) : 0;
            ?>
            <div class="swiper-slide position-relative">
                <a href="/product?p=<?= $p['id'] ?>" class="border-0 position-relative" style="border-radius:0;">
                    <span class="position-absolute top-0 end-0 bg-danger text-white px-2" style="font-size:x-small;z-index:10000;">
                        -<?= $discountPercent ?>%
                    </span>
                    <img src="/uploads/<?= htmlspecialchars($firstImage) ?>" 
                         style="height:150px; object-fit:contain;" 
                         class="w-100" 
                         alt="<?= htmlspecialchars($p['p_name']) ?>">
                    <div class="card-body p-2">
                        <h6 class="card-title text-truncate"><?= htmlspecialchars($p['p_name']) ?></h6>
                        <p class="m-0 text-muted">₦<?= number_format($p['p_selling_price'], 2) ?></p>
                        <p class="m-0 text-muted small"><del>₦<?= number_format($p['p_main_price'], 2) ?></del></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="swiper-button-prev"><i class="ri-arrow-left-s-line"></i></div>
        <div class="swiper-button-next"><i class="ri-arrow-right-s-line"></i></div>
    </div>
    <?php 
}

// ----------------------------------------------------------------
function my_cart($conn): array {
    $user = userauth($conn);
    if ($user['status'] !== true) {
        return [];
    }

    $stmt = $conn->prepare("
        SELECT 
            pc.*, 
            p.*, 
            m.user, 
            p.id AS product_id, 
            pc.id AS cart_id
        FROM productCart pc
        JOIN products p ON pc.p_id = p.id
        JOIN member m ON p.user_id = m.id
        WHERE pc.user_id = ?
        ORDER BY pc.id DESC
        LIMIT 20
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $user['id']);

    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}




function getProductSingle($conn) {
    $product_id = $_GET['p'] ?? '';
    
    $user_id = $_SESSION['user_id'] ?? 0;

    if (empty($product_id) || !is_numeric($product_id)) {
        return false;
    }

    // ✅ If you want to exclude products already in the user's own cart:
    $sql = "
        SELECT
            p.id AS product_id,
            p.p_image,
            p.user_id,
            p.p_name,
            p.p_category,
            p.p_main_price,
            p.p_selling_price,
            p.p_currency,
            p.p_discription,
            p.p_status,
            p.p_about,
            p.p_specifications,
            p.p_location,
            p.created_at,
            m.id AS member_id,
            m.email,
            m.user AS sellername,
            m.profile AS member_profile,
            m.whatsapp
        FROM products p
        JOIN member m ON p.user_id = m.id
        WHERE p.id = ?
          AND p.id NOT IN (
              SELECT p_id FROM productCart
          )
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->num_rows ? $result->fetch_assoc() : false;
    $stmt->close();
    return $row;
}



// ============================================
// DATA UPLOAD 
// ==============================================

function uploadProduct($conn) {
    header('Content-Type: application/json');

    if(userauth($conn)['status'] === false){
        return json_encode([
            "status" => false,
            "message" => "user account cannot be verified"
        ]);
    }


    $user_id = userauth($conn)["id"];
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'new';
    $price = $_POST['price'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $description = $_POST['description'] ?? '';
    $specification = $_POST['specification'] ?? '';
    $location = $_POST['location'] ?? '';

    // 🖼 Handle image uploads
    $uploadedFiles = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $originalName = basename($_FILES['images']['name'][$key]);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFileName = md5(uniqid('p_', true).time().userauth($conn)['email']). "." . $ext;
            $targetFile = $uploadDir . $newFileName;
            if (move_uploaded_file($tmp_name, $targetFile)) {
                $uploadedFiles[] = $newFileName;
            }
        }
    }

    // 📦 Insert product into DB
    $stmt = $conn->prepare("
        INSERT INTO products 
        (user_id, p_name, p_category, p_main_price, p_selling_price, p_discription, p_status, p_about, p_specifications, p_location, p_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $jsonImages = json_encode($uploadedFiles);
    $stmt->bind_param("issddssssss",
        $user_id,
        $name,
        $category,
        $amount,
        $price,
        $description,
        $status,
        $description,
        $specification,
        $location,
        $jsonImages
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "✅ Product uploaded successfully", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["message" => "Failed to save product", "status" => false]);
    }

    $stmt->close();
}
function updateProduct($conn, $productId) {
    header('Content-Type: application/json');

    if (userauth($conn)['status'] === false) {
        echo json_encode([
            "status" => false,
            "message" => "User account cannot be verified"
        ]);
        return;
    }

    $user_id = userauth($conn)["id"];
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'new';
    $price = $_POST['price'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $description = $_POST['description'] ?? '';
    $specification = $_POST['specification'] ?? '';
    $location = $_POST['location'] ?? '';
    
    // Handle old images
    $existingImages = [];
    if (!empty($_POST['existing_images'])) {
        $existingImages = json_decode($_POST['existing_images'], true);
    }

    // Handle new image uploads
    $uploadDir = __DIR__ . '/../uploads/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $newUploadedFiles = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $originalName = basename($_FILES['images']['name'][$key]);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFileName = md5(uniqid('p_', true) . time() . userauth($conn)['email']) . "." . $ext;
            $targetFile = $uploadDir . $newFileName;
            if (move_uploaded_file($tmp_name, $targetFile)) {
                $newUploadedFiles[] = $newFileName;
            }
        }
    }

    // Merge existing + new images
    $finalImageList = array_merge($existingImages, $newUploadedFiles);
    $jsonImages = json_encode($finalImageList);

    // Update the product
    $stmt = $conn->prepare("
        UPDATE products SET 
            p_name = ?, 
            p_category = ?, 
            p_main_price = ?, 
            p_selling_price = ?, 
            p_discription = ?, 
            p_status = ?, 
            p_about = ?, 
            p_specifications = ?, 
            p_location = ?, 
            p_image = ?
        WHERE id = ? AND user_id = ?
    ");

    $stmt->bind_param("ssddssssssii",
        $name,
        $category,
        $amount,
        $price,
        $description,
        $status,
        $description,
        $specification,
        $location,
        $jsonImages,
        $productId,
        $user_id
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "✅ Product updated successfully"]);
    } else {
        echo json_encode(["status" => false, "message" => "Failed to update product"]);
    }

    $stmt->close();
}



function deleteImageFile(string $filename): array {
    $filename = basename($filename); // sanitize filename to avoid directory traversal
    $filepath = __DIR__ . '/../uploads/' . $filename; // adjust path as needed

    if (!file_exists($filepath)) {
        return ['success' => false, 'message' => 'File not found'];
    }

    if (!unlink($filepath)) {
        return ['success' => false, 'message' => 'Failed to delete file'];
    }

    return ['success' => true, 'message' => 'File deleted successfully'];
}


// ==============================================================================
// cart
// =============================================================================
function addToCart($id){
    // ensure session is active
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // reject empty ids
    if ($id === null || $id === '') return false;

    // normalize id (use int when numeric)
    $id = is_numeric($id) ? (int)$id : trim((string)$id);

    // ensure cart is an array
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // add if not already present
    if (!in_array($id, $_SESSION['cart'], true)) {
        $_SESSION['cart'][] = $id;
        return true;
    }
    return false;
}
function removeFromCart($id) {
    // ensure session is active
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // reject empty ids
    if ($id === null || $id === '') return false;

    // normalize id (use int when numeric)
    $id = is_numeric($id) ? (int)$id : trim((string)$id);

    // check if cart exists
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return false;
    }

    // remove item if present
    $key = array_search($id, $_SESSION['cart'], true);
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
        return true;
    }
    return false;
}


function fetchCartItems($conn) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $cartItems = $_SESSION['cart'] ?? [];
    // keep only numeric ids
    $cartItems = array_values(array_filter($cartItems, function($v){ return is_numeric($v) || is_int($v); }));
    if (empty($cartItems)) return [];
    $data = [];
        // return product only if NOT already present in productCart for this user
        $sql = "
            SELECT *
            FROM products where id = ? AND id NOT IN (
                SELECT p_id FROM productCart 
            )
        ";
        $stmt = $conn->prepare($sql);
        if($stmt === false) return [];

        foreach ($cartItems as $itemId) {
            $id = (int)$itemId;
            $stmt->bind_param("i", $id);
            if (! $stmt->execute()) continue;
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            if ($row) $data[] = $row;
        }

        $stmt->close();
    return $data;
}


function fetchNotificationsCount($conn) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $auth = userauth($conn);
    if ($auth['status'] === false) return 0;

    $user_id = $auth['id'];

    $sql = "SELECT COUNT(*) AS count 
            FROM notifications 
            WHERE user_id = ? AND status = 'unread'";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return 0;

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }

    $res = $stmt->get_result();
    $data = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return (int)($data['count'] ?? 0);
}


function fetchNotifications($conn, $limit = 0) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $auth = userauth($conn);
    if ($auth['status'] === false) return [];

    $user_id = $auth['id'];

    // 1️⃣ Fetch notifications
    $sql = "SELECT * 
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY id DESC 
            LIMIT ?, 20";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return [];

    $stmt->bind_param("ii", $user_id, $limit);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    // 2️⃣ Mark fetched notifications as "read"
    if (!empty($data)) {
        $ids = array_column($data, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = 'i' . str_repeat('i', count($ids));

        $updateSql = "UPDATE notifications 
                      SET status = 'read' 
                      WHERE user_id = ? 
                      AND id IN ($placeholders)";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt !== false) {
            $updateStmt->bind_param($types, $user_id, ...$ids);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }

    return $data;
}

function addNotification($conn, $user_id, $message) {
    $sql = "INSERT INTO notifications (user_id, message, status, created_at) 
            VALUES (?, ?, 'unread', NOW())";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) return false;

    $stmt->bind_param("is", $user_id, $message);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

// ==========================================================
// UPGRADE MEMBER
// ==========================================================
 function upgrade($conn, $data) {
    header('Content-Type: application/json');

    if (userauth($conn)['status'] === false) {
        echo json_encode([
            "status" => false,
            "message" => "User account cannot be verified"
        ]);
        return;
    }

    $user_id = userauth($conn)["id"];
    $username = $data->username ?? '';
    $first_name = $data->first_name ?? '';
    $last_name = $data->last_name ?? '';
    $phone = $data->phone ?? '';
    $whatsapp = $data->whatsapp ?? '';
    $country = $data->country ?? '';
    $profile = userauth($conn)['profile'] ?? [];
    $profile['first_name'] = $first_name;
    $profile['last_name'] = $last_name;
    $profile['phone'] = $phone;
    $profile['whatsapp'] = $whatsapp;
    $profile['country'] = $country;
    $profile_json = json_encode($profile);
    $stmt = $conn->prepare("
        UPDATE member SET 
            user = ?, 
            profile=?,
            whatsapp = ?,
            status = 'active' 
           WHERE id = ?
    ");

    $stmt->bind_param("sssi",
        $username,
        $profile_json,
        $whatsapp,
        $user_id
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "✅ Account yes upgraded successfully"]);
    } else {
        echo json_encode(["status" => false, "message" => "Failed to upgrade account"]);
    }
    $stmt->close();
}