<?php

const DB_SERVER     = "localhost";
const DB_USER       = "owner01";
const DB_PASSWORD   = "123456";
const DB_NAME       = "project01";

//建立連線
function create_connection()
{
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        echo json_encode(["state" => false, "message" => "連線錯誤"]);
        exit;
    }
    return $conn;
}


//取得JSON的資料
function get_json_input()
{
    $data = file_get_contents("php://input");
    return json_decode($data, true);
}


//回覆JSON訊息
//state: 狀態(成功或失敗) ; message: 訊息內容 ; data: 回傳資料(可有可無)
function respond($state, $message, $data = null)
{
    echo json_encode(["state" => $state, "message" => $message, "data" => $data]);
}

//會員註冊
//{"username" : "owner01", "password" : "123456", "email" : "owner01@test.com"..........}
// {"state" : true, "message" : "註冊成功"}
// {"state" : false, "message" : "新增失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function register_user()
{
    $input = get_json_input();

    if (isset($input["username"], $input["password"], $input["email"])) {

        $p_username = $input["username"];
        $p_password = password_hash(trim($input["password"]), PASSWORD_DEFAULT);
        $p_email = trim($input["email"]);
        $p_birthDate = $input["birthDate"];
        // if(isset($input["profilePhoto"])){
        //     $p_profilePhoto = date("YmdHis")."_".$input["profilePhoto"];
        // }else{
        //     $p_profilePhoto = "mountain001.jpg";
        // }


        if ($p_username && $p_password && $p_email) {
            $conn = create_connection();

            // $stmt = $conn->prepare("INSERT INTO member(username, password, email, birthDate, profilePhoto) VALUES(?, ?, ?, ?, ?)");
            // $stmt->bind_param("sssss", $p_username, $p_password, $p_email, $p_birthDate, $p_profilePhoto);

            try {
                $inserUserStmt = $conn->prepare("INSERT INTO user(username, password, email, birthDate) VALUES(?, ?, ?, ?)");
                $inserUserStmt->bind_param("ssss", $p_username, $p_password, $p_email, $p_birthDate);


                if ($inserUserStmt->execute()) {
                    $userId = $inserUserStmt->insert_id;
                    $insertProfileStmt = $conn->prepare("INSERT INTO user_profile (userId) VALUES (?)");
                    $insertProfileStmt->bind_param('i', $userId);
                    $insertProfileStmt->execute();
                    $insertProfileStmt->close();
                    respond(true, "註冊成功");
                } else {
                    respond(false, "註冊失敗");
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) { // MySQL duplicate entry error code
                    respond(false, "該帳號已被註冊，請重新輸入");
                } else {
                    respond(false, "系統錯誤，請稍後再試");
                }
            }

            $inserUserStmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}


// 圖片上傳
function upload_profilePhoto()
{
    // echo $_FILES['file']['name'].'<br>';
    // echo $_FILES['file']['type'].'<br>';
    // echo $_FILES['file']['tmp_name'].'<br>';
    // echo $_FILES['file']['size'].'<br>';
    // echo $_FILES['file']['error'].'<br>'; 

    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
        if ($_FILES['file']['type'] == 'image/jpeg' || $_FILES['file']['type'] == 'image/png') {
            // 檔案(圖片)上傳至伺服器(後端主機)

            $filename = date("YmdHis") . "_" . $_FILES['file']['name'];
            // 先設定路徑
            $location = 'upload/' . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
                $datainformation = array();
                $datainformation["state"] = true;
                $datainformation["message"] = "檔案上傳成功!";
                $datainformation["name"] = $_FILES['file']['name'];
                $datainformation["location"] = $location;
                $datainformation["type"] = $_FILES['file']['type'];
                $datainformation["tmp_name"] = $_FILES['file']['tmp_name'];
                $datainformation["size"] = $_FILES['file']['size'];
                $datainformation["error"] = $_FILES['file']['error'];

                echo json_encode($datainformation);
            } else {
                $errorinfo = array();
                $errorinfo["state"] = false;
                $errorinfo["message"] = "檔案上傳失敗!";
                $errorinfo["error"] = $_FILES['file']['error'];

                echo json_encode($errorinfo);
            }
        } else {
            echo '{"state" : false, "message" : "上傳格式不為jpeg or png!"}';
        }
    } else {
        echo '{"state" : false, "message" : "檔案不存在!"}';
    }
}


//會員登入
//{"username" : "owner01", "password" : "123456"}
// {"state" : true, "message" : "登入成功", "data" : "使用者資訊"}
// {"state" : false, "message" : "登入失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function login_user()
{

    $input = get_json_input();

    if (isset($input["username"], $input["password"])) {
        $p_username = trim($input["username"]);
        $p_password = trim($input["password"]);

        if ($p_username && $p_password) {
            $conn = create_connection();

            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->bind_param("s", $p_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($p_password, $row["password"])) {
                    $uid01 = substr(hash('sha256', time()), rand(10, 15), rand(19, 29)) . substr(bin2hex(random_bytes(16)), rand(5, 10), rand(5, 10));
                    $update_stmt = $conn->prepare("UPDATE user SET uid01 = ? WHERE username = ?");
                    $update_stmt->bind_param("ss", $uid01, $p_username);

                    if ($update_stmt->execute()) {
                        $user_stmt = $conn->prepare("SELECT * FROM user u LEFT JOIN user_profile p ON u.id = p.userId WHERE username = ?");
                        $user_stmt->bind_param("s", $p_username);
                        $user_stmt->execute();
                        $user_data = $user_stmt->get_result()->fetch_assoc();
                        unset($user_data["password"]);
                        respond(true, "登入成功", $user_data);
                        $user_stmt->close();
                    } else {
                        respond(false, "登入失敗, UID更新失敗");
                    }
                    $update_stmt->close();
                } else {
                    respond(false, "登入失敗, 帳號或密碼錯誤");
                }
            } else {
                respond(false, "登入失敗, 此帳號不存在");
            }

            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}



//驗證Uid01
//{"uid01" : "owner01"}
// {"state" : true, "message" : "驗證成功", "data" : "使用者資訊"}
// {"state" : false, "message" : "驗證失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function check_uid()
{
    $input = get_json_input();

    if (isset($input["uid01"])) {
        $p_uid = $input["uid01"];

        if ($p_uid) {
            $conn = create_connection();

            $stmt = $conn->prepare("SELECT * FROM user u LEFT JOIN user_profile p ON u.id = p.userId WHERE uid01 = ?");
            $stmt->bind_param('s', $p_uid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $userdata = $result->fetch_assoc();
                unset($userdata["password"]);
                respond(true, "驗證成功", $userdata);
            } else {
                respond(false, "驗證失敗");
            }
            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}


//帳號是否存在(前台註冊)
// {"username" : "owner01"}
// {"state" : true, "message" : "帳號不存在, 可以使用"}
// {"state" : false, "message" : "帳號已存在, 不可以使用"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function check_username()
{
    $input = get_json_input();

    if (isset($input["username"])) {
        $p_username = $input["username"];
        if ($p_username) {

            $conn = create_connection();

            $stmt = $conn->prepare("SELECT username FROM user WHERE username = ?");
            $stmt->bind_param("s", $p_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                //帳號已經存在 不可以使用
                respond(false, "該帳號已經存在，不可以使用，請重新輸入");
            } else {
                //帳號不存在 可以使用
                respond(true, "該帳號不存在，可以使用");
            }
            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}

//取得所有會員資料(管理後臺)
// input:none
// {"state" : true, "message" : "取得所有會員資料成功", "data": 所有使用者資訊}
// {"state" : false, "message" : "取得所有會員資料失敗"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function get_all_user_data()
{
    $conn = create_connection();

    // 取得前端傳來的分頁參數
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;  // 預設 25 筆
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;  // 預設第 1 頁
    $offset = ($page - 1) * $limit;

    // 取得資料總數
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM user");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_count = $count_result->fetch_assoc()['total'];

    // 查詢會員 + 會員詳細資料
    $sql = "SELECT 
        u.id, u.username, u.email, u.birthDate, 
        u.createdAt AS user_createdAt, u.updatedAt AS user_updatedAt,
        p.full_name, p.city, p.area, p.address, p.photo, p.orderCount, p.membershipType, 
        p.createdAt AS profile_createdAt, p.updatedAt AS profile_updatedAt
        FROM user u
        LEFT JOIN user_profile p ON u.id = p.userId
        WHERE u.level <> 50
        ORDER BY u.id ASC
        LIMIT ? OFFSET ?";


    //$stmt = $conn->prepare("SELECT * FROM user ORDER BY ID ASC LIMIT ? OFFSET ?");
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mydata = array();
        while ($row = $result->fetch_assoc()) {
            unset($row["password"]);
            unset($row["uid01"]);
            $mydata[] = $row;
        }
        respond(true, "取得所有會員資料成功", array(
            "data" => $mydata,
            "total_count" => $total_count
        ));
    } else {
        //查無資料
        respond(false, "取得所有會員資料失敗");
    }
    $stmt->close();
    $conn->close();
}

function search_user_data()
{
    $input = get_json_input(); // 取得 JSON 輸入

    if (isset($input["query"])) {
        $query = trim($input["query"]); // 取得搜尋關鍵字並去除空白

        // 建立 SQL 語句 (使用參數化查詢)
        $sql = "SELECT 
                u.id, u.username, u.email, u.birthDate, 
                u.createdAt AS user_createdAt, u.updatedAt AS user_updatedAt,
                p.full_name, p.city, p.area, p.address, p.photo, p.orderCount, p.membershipType, 
                p.createdAt AS profile_createdAt, p.updatedAt AS profile_updatedAt
            FROM user u
            LEFT JOIN user_profile p ON u.id = p.userId
            WHERE u.level <> 50";

        $params = [];
        $types = "";
    } else {
        respond(false, "欄位錯誤");
        return;
    }

    if (!empty($query)) {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.birthDate LIKE ? OR 
                       u.createdAt LIKE ? OR u.updatedAt LIKE ?)";
        $searchQuery = "%$query%";
        $params = [$searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery];
        $types = "sssss"; // 5 個字串參數
    }

    $sql .= " ORDER BY u.id ASC"; // 確保結果按 ID 排序

    // 建立資料庫連線
    $conn = create_connection();
    $stmt = $conn->prepare($sql);

    // 如果有搜尋條件，則綁定參數
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mydata = [];
        while ($row = $result->fetch_assoc()) {
            unset($row["password"]); // 移除敏感資訊
            unset($row["uid01"]);
            $mydata[] = $row;
        }
        respond(true, "取得所有會員資料成功", $mydata);
    } else {
        respond(false, "查無資料");
    }

    $stmt->close();
    $conn->close();
}

// 更新會員資料(前臺)
// input: {"id" : "xxxxxx", "email" : "xxxxxx"}
// {"state" : true, "message" : "會員資料更新成功"}
// {"state" : false, "message" : "會員資料更新失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function update_user_data()
{
    $input = get_json_input();

    if (isset($input["id"])) {
        $p_id = $input["id"];
        $newPhoto = $input["photo"] ?? "";
        $oldPhoto = $input["delete_old_photo"] ?? "";

        if ($p_id) {

            // 若有新圖片，刪除舊圖片
            if (!empty($newPhoto) && !empty($oldPhoto) && $newPhoto !== $oldPhoto) {
                $oldPhotoPath = "upload/" . $oldPhoto;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath); // 刪除舊圖片
                }
            }

            $allowed_fields = [
                "username" => "user",
                "email" => "user",
                "password" => "user", // 密碼加密要特別處理
                "full_name" => "user_profile",
                "city" => "user_profile",
                "area" => "user_profile",
                "address" => "user_profile",
                "birthDate" => "user",
                "photo" => "user_profile"
            ];

            //$users_updates = [];
            //$profiles_updates = [];
            $fields = [];
            $params = [];
            $types = "";

            foreach ($input as $field => $value) {
                if (!empty($value) && array_key_exists($field, $allowed_fields)) {
                    if ($field === "password") {
                        // 只有當密碼有輸入時才加密
                        $hashed_password = password_hash(trim($value), PASSWORD_DEFAULT);
                        $fields[] = "u.password = ?";
                        $params[] = $hashed_password;
                        $types .= "s";
                        continue; // 避免密碼欄位被重複加入
                    } else if ($field === "photo") {
                        // 只有當會員頭照有上傳成功時, 檔案名稱才做處裡
                        $filename = date("YmdHis") . "_" . $value;
                        $fields[] = "p.photo = ?";
                        $params[] = $filename;
                        $types .= "s";
                    } else if ($allowed_fields[$field] == "user") {
                        $fields[] = "u.$field = ?";
                        $params[] = trim($value);
                        $types .= "s";
                    } else if ($allowed_fields[$field] == "user_profile") {
                        $fields[] = "p.$field = ?";
                        $params[] = trim($value);
                        $types .= "s";
                    }
                }
            }

            //if ($p_id && $p_email) {
            if (count($fields) > 0) {

                $fields[] = "u.updatedAt = NOW()";
                $fields[] = "p.updatedAt = NOW()";

                // $sql = "UPDATE user u JOIN user_profile p ON u.id = p.userId SET ";
                // $sql .= implode(", ", array_merge($users_updates, $profiles_updates));
                // $sql .= " WHERE u.id = ?";
                $sql = "UPDATE user u 
                    JOIN user_profile p ON u.id = p.userId 
                    SET " . implode(", ", $fields) . "
                    WHERE u.id = ?";

                $params[] = $p_id;
                $types .= "i";
                $conn = create_connection();

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);


                //  $stmt = $conn->prepare("UPDATE user u JOIN user_profile p ON u.id = p.userId SET email = ? WHERE ID = ?");
                //  $stmt->bind_param("si", $p_email, $p_id);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        respond(true, "會員資料更新成功");
                    } else {
                        respond(false, "會員資料更新失敗, 並無資料被更新!");
                    }
                } else {
                    respond(false, "會員資料更新失敗與相關錯誤訊息");
                }
                $stmt->close();
            } else {
                respond(false, "無可更新的欄位");
            }
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}


// 刪除會員資料
// input: {"id" : "xxxxxx"}
// {"state" : true, "message" : "會員資料刪除成功"}
// {"state" : false, "message" : "會員資料刪除失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function delete_user_data()
{
    $input = get_json_input();

    if (isset($input["id"])) {
        $p_id = $input["id"];

        if ($p_id) {

            $conn = create_connection();

            $stmt = $conn->prepare("DELETE FROM member WHERE ID = ?");
            $stmt->bind_param("i", $p_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 1) {
                    respond(true, "會員資料刪除成功");
                } else {
                    respond(false, "會員資料刪除失敗, 並無資料被刪除!");
                }
            } else {
                respond(false, "會員資料刪除失敗與相關錯誤訊息");
            }
            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'register':
            register_user();
            break;
        case 'login':
            login_user();
            break;
        case 'chkuid':
            check_uid();
            break;
        case 'chkuni':
            check_username();
            break;
        case 'update':
            update_user_data();
            break;
        case 'upload':
            upload_profilePhoto();
            break;
        case 'searchdata':
            search_user_data();
            break;
        default:
            respond(false, "無效的操作!");
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'getalldata':
            get_all_user_data();
            break;
        case 'searchdata':
            search_user_data();
            break;
        default:
            respond(false, "無效的操作!");
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'delete':
            delete_user_data();
            break;
        default:
            respond(false, "無效的操作!");
    }
} else {
    respond(false, "無效的請求方法!");
}
