(function () {


    //from W3C--讀取cookie
    function getCookie(cname) {
        let name = cname + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    //檢查UID是否存在, 若沒有則導向登入畫面
    if (!getCookie("Uid01")) {
        Swal.fire({
            title: "請先登入會員",
            showDenyButton: false,
            showCancelButton: false,
            confirmButtonText: "確認",
            denyButtonText: `Don't save`,
            allowOutsideClick: false
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                location.href = "250115-2_v1.html"
            }
        });

        return;
    }

    //若UID存在, 傳遞至後端執行驗證
    var JSONdata = {};
    JSONdata["uid01"] = getCookie("Uid01");

    $.ajax({
        type: "POST",
        url: "member_control_api_v1.php?action=chkuid",
        data: JSON.stringify(JSONdata),
        dataType: "json",
        success: function (data) {
            if (data.state) {
                //顯示歡迎訊息
                $("#s02_username_showtext").removeClass("d-none");
                $("#s02_username_text").text(data.data.Username);

                //顯示登出按鈕
                $("#s02_logout_btn").removeClass("d-none");
            } else {
                Swal.fire({
                    title: "請先登入會員",
                    showDenyButton: false,
                    showCancelButton: false,
                    confirmButtonText: "確認",
                    denyButtonText: `Don't save`,
                    allowOutsideClick: false
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        location.href = "250115-2_v1.html"
                    }
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "API介接錯誤",
                text: "member_control_api_v1.php?action=chkuid",
                icon: "error"
            });
        }
    });

    //from W3C--寫入cookie
    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    //登出按鈕監聽
    $("#s02_logout_btn").click(function () {
        setCookie("Uid01", "", 7);
        Swal.fire({
            //title: data.message,
            text: "已登出成功!!",
            icon: "success"
        }).then((result) => {
            location.href = "250115-2_v1.html";
        });

    });


})();