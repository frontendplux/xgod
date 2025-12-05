<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../plugin/bootstrap-5.3.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../plugin/RemixIcon_Fonts_v4.1.0/rimix-icon/remixicon.css">
    <title>Document</title>
</head>
<body class="bg-light">
    <header class="bg-white p-2" >
        <h1 class="bg-white container p-2 text-uppercase">Xgod</h1>
    </header>
    <div class="p-3">
        <div style="max-width: 600px;" class="bg-white my-3 mx-auto p-3">
        <div>
            <div class="text-center h4 mb-0 my-1">
                Welcome to Xgod
            </div>
            <div class="">
                <div class="text-center py-2">
                    Transaction made easy and posible with us
                </div>
                <div>
                    <input type="text" id="email" class="form-control" placeholder="Username or Email address">
                </div>
            </div>
            <div class="my-2">
                <button class="w-100 btn border-0 p-2 btn-warning text-capitalize" onclick="login()">
                    continue to password
                </button>
            </div>
            <div class="text-center my-3" style="font-size: small;">
                by signing in into xgod you agreed to our
                <a href="">Terms&nbsp;and&nbsp;Condition</a>
            </div>
        </div>
    </div>
    </div>
    <script src="plugin/custom/scripts.js"></script>
    <script>
       async function login(){
            const email=document.getElementById("email").value.trim();
            if (email == ""){
                alerta("info", "provide the valid email address")
            }
            else{
                const
                const req=await fetch("/api/login",{
                    method: "post",
                    body: JSON.stringify({email:email})
                }).then(res => res.json());
                console.log(req);
                if(req[0] === true){
                    if(req[1] == 1)  window.location.href="/password?u="+email;
                    else window.location.href="/create-password?u="+email;
                }
                else{
                    alert("danger", req.message)
                }
            }
        }
    </script>
</body>
</html>