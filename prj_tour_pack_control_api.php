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

//新增品項
//{"title" : "example01", "description" : "example01", "country" : "義大利", "city" : "羅馬", "duration" : "7", "price" : "12000", "currency" : "台幣", "available_seats" : "3", "start_date" : "2025-03-10", "end_date" : "2025-03-20", "status" : "active"}
// {"state" : true, "message" : "註冊成功"}
// {"state" : false, "message" : "新增失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function insert_item()
{
    $input = get_json_input();
    if (isset($input)) {
        $p_title = $input["title"];
        $p_description = $input["description"] ?? null;
        $p_country = $input["country"];
        $p_city_first = $input["city_first"];
        $p_city_second = $input["city_second"]  ?? null;
        $p_duration = $input["duration"];
        $p_price = $input["price"];
        $p_currency = $input["currency"];
        $p_available_seats = $input["available_seats"];
        $p_start_date = $input["start_date"];
        $p_end_date = $input["end_date"];
        //$p_status = $input["status"];
        $p_photo_first = $input["photo_first"];
        $p_photo_second = $input["photo_second"]  ?? null;
        $p_photo_third = $input["photo_third"]  ?? null;
        $p_photo_fourth = $input["photo_fourth"]  ?? null;

        
        // if ($input["photo"] == "") {

        //     $p_photo = generate_profilePhoto();

        //     //$p_photo = date("YmdHis") . "_" . $imageName;
        // } else {
        //     $p_photo = date("YmdHis") . "_" . $input["photo"];
        // }


        // if ($p_title && $p_description && $p_country && $p_city_first && $p_duration && $p_price && $p_currency && $p_available_seats && $p_start_date && $p_end_date && $p_photo_first) {
        if ($p_title) {
            $conn = create_connection();

            // $stmt = $conn->prepare("INSERT INTO tour_packages(title, description, country, city_first, duration, price, currency, available_seats, start_date, end_date, photo_first) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // $stmt->bind_param("ssssiisisss", $p_title, $p_description, $p_country, $p_city_first, $p_city_second, $p_duration, $p_price, $p_currency, $p_available_seats, $p_start_date, $p_end_date, $p_photo_first);

            $stmt = $conn->prepare("INSERT INTO tour_packages(title, description, country, city_first, city_second, duration, price, currency, available_seats, start_date, end_date, photo_first, photo_second, photo_third, photo_fourth) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssiisissssss", $p_title, $p_description, $p_country, $p_city_first, $p_city_second, $p_duration, $p_price, $p_currency, $p_available_seats, $p_start_date, $p_end_date, $p_photo_first, $p_photo_second, $p_photo_third, $p_photo_fourth);

            if ($stmt->execute()) {
                respond(true, "新增成功");
            } else {
                respond(false, "新增失敗");
            }
            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空");
        }
    } else {
        respond(false, "欄位名稱錯誤");
    }
}

function generate_profilePhoto()
{

    // 連接資料庫
    $conn = create_connection();

    // 定義資料夾路徑
    $uploadFolder = 'upload';
    if (!is_dir($uploadFolder)) {
        mkdir($uploadFolder, 0777, true);  // 如果資料夾不存在則創建
    }

    // 圖片 URL
    $image_url = "https://lipsum.app/random/1600x900";

    // // 隨機生成圖片名稱
    $image_name = "image_" . date("YmdHis") . "_" . ".jpg";

    // // 設定圖片保存路徑
    $image_path = $uploadFolder . "/" . $image_name;

    // // 使用 file_get_contents() 下載圖片並保存
    $image_data = file_get_contents($image_url);
    if ($image_data === false) {
        die("Failed to download image.");
    }

    // // 將圖片寫入 upload 資料夾
    file_put_contents($image_path, $image_data);

    return $image_name;


    // // 插入圖片資訊到資料庫
    // $sql = "INSERT INTO photo (image_name, image_path) VALUES ('$image_name', '$image_path')";

    // if ($conn->query($sql) === TRUE) {
    //     echo "New record created successfully";
    // } else {
    //     echo "Error: " . $sql . "<br>" . $conn->error;
    // }

    // // 關閉資料庫連接
    // $conn->close();


    // // 發送 GET 請求獲取圖片 URL
    // $response = file_get_contents($imageUrl);
    // if ($response === FALSE) {
    //     die("無法從 " . $imageUrl . " 獲取數據");
    // }

    // $data = json_decode($response, true);
    // $imageUrl = $data['image'];  // 提取圖片 URL

    // // 下載圖片
    // //$imageContent = file_get_contents($imageUrl);
    // $imageContent = curl_download($imageUrl);
    // if ($imageContent === true) {
    //     die("無法下載圖片");
    // }

    // // 生成圖片的本地文件名
    // $imageName = basename($imageUrl);
    // $imagePath = $uploadFolder . '/' . $imageName;

    // // 保存圖片到本地
    // file_put_contents($imagePath, $imageContent);

    // // 插入資料庫
    // $uploadTime = date('Y-m-d H:i:s');
    // $stmt = $conn->prepare("INSERT INTO photo (image_name, image_path) VALUES (?, ?)");
    // $stmt->bind_param('ss', $image_name, $image_path);

    // if ($stmt->execute()) {
    //     echo "圖片已成功下載並保存到 " . $image_path . "，並已寫入資料庫。";
    // } else {
    //     echo "資料庫操作錯誤: " . $stmt->error;
    // }

    // // // 關閉資料庫連接
    // $stmt->close();
    // $conn->close();

    // function curl_download($url)
    // {
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    //     $data = curl_exec($ch);
    //     curl_close($ch);
    //     return $data;
    // }
}




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

            //$filename = date("YmdHis") . "_" . $_FILES['file']['name'];
            //$filename = date("YmdHis") . "_" . round(microtime(true) * 1000) . "_" . $_FILES['file']['name'];
            //$filename = date("Ymd") . str_pad(round((microtime(true) - floor(microtime(true))) * 1000), 3, '0', STR_PAD_LEFT) . "_" . $_FILES['file']['name'];
            $filename = $_FILES['file']['name'];

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


//會員登入----目前用不到----
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

            $stmt = $conn->prepare("SELECT * FROM member WHERE Username = ?");
            $stmt->bind_param("s", $p_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($p_password, $row["Password"])) {
                    $uid01 = substr(hash('sha256', time()), rand(10, 15), rand(19, 29)) . substr(bin2hex(random_bytes(16)), rand(5, 10), rand(5, 10));
                    $update_stmt = $conn->prepare("UPDATE member SET Uid01 = ? WHERE Username = ?");
                    $update_stmt->bind_param("ss", $uid01, $p_username);

                    if ($update_stmt->execute()) {
                        $user_stmt = $conn->prepare("SELECT * FROM member WHERE Username = ?");
                        $user_stmt->bind_param("s", $p_username);
                        $user_stmt->execute();
                        $user_data = $user_stmt->get_result()->fetch_assoc();
                        unset($user_data["Password"]);
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


//驗證Uid01----目前用不到----
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

            $stmt = $conn->prepare("SELECT Username, Email, Uid01, Created_at FROM member WHERE Uid01 = ?");
            $stmt->bind_param('s', $p_uid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $userdata = $result->fetch_assoc();
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


//行程名稱是否存在
// {"username" : "owner01"}
// {"state" : true, "message" : "帳號不存在, 可以使用"}
// {"state" : false, "message" : "帳號已存在, 不可以使用"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function check_title()
{
    $input = get_json_input();

    if (isset($input["title"])) {
        $p_title = $input["title"];
        if ($p_title) {

            $conn = create_connection();

            $stmt = $conn->prepare("SELECT title FROM tour_packages WHERE title = ?");
            $stmt->bind_param("s", $p_title);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                //帳號已經存在 不可以使用
                respond(false, "該名稱已經存在，請調整或重新輸入");
            } else {
                //帳號不存在 可以使用
                respond(true, "該名稱不存在，可以使用");
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

//取得所有行程資料
// input:none
// {"state" : true, "message" : "取得所有會員資料成功", "data": 所有使用者資訊}
// {"state" : false, "message" : "取得所有會員資料失敗"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function get_all_item_data()
{
    $conn = create_connection();

    $stmt = $conn->prepare("SELECT * FROM tour_packages ORDER BY ID ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $mydata = array();
        while ($row = $result->fetch_assoc()) {
            $mydata[] = $row;
        }
        respond(true, "取得所有資料成功", $mydata);
    } else {
        //查無資料
        respond(false, "取得所有資料失敗");
    }
    $stmt->close();
    $conn->close();
}

// 更新行程資料
// input: {"id" : "xxxxxx", "email" : "xxxxxx"}
// {"state" : true, "message" : "會員資料更新成功"}
// {"state" : false, "message" : "會員資料更新失敗與相關錯誤訊息"}
// {"state" : false, "message" : "欄位錯誤"}
// {"state" : false, "message" : "欄位不得為空白"}
function update_item_data()
{
    $input = get_json_input();

    if (isset($input["id"])) {
        $p_id = $input["id"];
        $newPhoto_first = $input["photo_first"] ?? "";
        $oldPhoto_first = $input["delete_old_photo_first"] ?? "";
        $newPhoto_second = $input["photo_second"] ?? "";
        $oldPhoto_second = $input["delete_old_photo_second"] ?? "";
        $newPhoto_third = $input["photo_third"] ?? "";
        $oldPhoto_third = $input["delete_old_photo_third"] ?? "";
        $newPhoto_fourth = $input["photo_fourth"] ?? "";
        $oldPhoto_fourth = $input["delete_old_photo_fourth"] ?? "";

        if ($p_id) {

            // 若有新圖片，刪除舊圖片
            if (!empty($newPhoto_first) && !empty($oldPhoto_first) && $newPhoto_first !== $oldPhoto_first) {
                $oldPhotoPath = "upload/" . $oldPhoto_first;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath); // 刪除舊圖片
                }
            }
            if (!empty($newPhoto_second) && !empty($oldPhoto_second) && $newPhoto_second !== $oldPhoto_second) {
                $oldPhotoPath = "upload/" . $oldPhoto_second;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath); // 刪除舊圖片
                }
            }
            if (!empty($newPhoto_third) && !empty($oldPhoto_third) && $newPhoto_third !== $oldPhoto_third) {
                $oldPhotoPath = "upload/" . $oldPhoto_third;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath); // 刪除舊圖片
                }
            }
            if (!empty($newPhoto_fourth) && !empty($oldPhoto_fourth) && $newPhoto_fourth !== $oldPhoto_fourth) {
                $oldPhotoPath = "upload/" . $oldPhoto_fourth;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath); // 刪除舊圖片
                }
            }

            $allowed_fields = [
                "title" => "tour_packages",
                "description" => "tour_packages",
                "country" => "tour_packages",
                "city_first" => "tour_packages",
                "city_second" => "tour_packages",
                "duration" => "tour_packages",
                "price" => "tour_packages",
                "curreny" => "tour_packages",
                "available_seats" => "tour_packages",
                "start_date" => "tour_packages",
                "end_date" => "tour_packages",
                "photo_first" => "tour_packages",
                "photo_second" => "tour_packages",
                "photo_third" => "tour_packages",
                "photo_fourth" => "tour_packages",
                "status" => "tour_packages"
                
            ];

            //$users_updates = [];
            //$profiles_updates = [];
            $fields = [];
            $params = [];
            $types = "";

            foreach ($input as $field => $value) {
                if (!empty($value) && array_key_exists($field, $allowed_fields)) {
                    if ($allowed_fields[$field] == "tour_packages") {
                        $fields[] = "t.$field = ?";
                        $params[] = trim($value);

                        if (is_numeric($value)){
                            $types .= "i";
                        }else{
                            $types .= "s";
                        }
                    }
                }
            }

            //if ($p_id && $p_email) {
            if (count($fields) > 0) {

                $fields[] = "t.updated_at = NOW()";
                

                // $sql = "UPDATE user u JOIN user_profile p ON u.id = p.userId SET ";
                // $sql .= implode(", ", array_merge($users_updates, $profiles_updates));
                // $sql .= " WHERE u.id = ?";
                $sql = "UPDATE tour_packages AS t
                    SET " . implode(", ", $fields) . "
                    WHERE t.id = ?";

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


// 刪除會員資料----目前用不到----
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
        case 'insert':
            insert_item();
            break;
        case 'login':
            login_user();
            break;
        case 'chkuid':
            check_uid();
            break;
        case 'chkuni':
            check_title();
            break;
        case 'update':
            update_item_data();
            break;
        case 'upload':
            upload_profilePhoto();
            break;
        default:
            respond(false, "無效的操作!");
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'getalldata':
            get_all_item_data();
            break;
        case 'newphoto':
            generate_profilePhoto();
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
