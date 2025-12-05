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
    <header>
        <h1 class="bg-white p-2">BoosterX</h1>
    </header>
    <div style="max-width: 400px;" class="bg-white my-3 mx-auto p-3">
        <div>
                <div class="text-center fw-bold text-capitalize py-2">
                   create password?
                </div>
            <div class="text-center h5 text-muted my-1">
                Welcome to Boosterx Admin
            </div>
            <div class="my-2">
                <div>
                    <input type="password" id="email" class="form-control text-center" placeholder="Create Pro">
                </div>
            </div>
            <div class="my-2">
                <button class="w-100 btn border-0 p-2 btn-warning" onclick="login()">
                    continue to admin
                </button>
            </div>
            <div class="text-center my-3" style="font-size: small;">
                by signing in into BoosterX you aggred to the 
                <a href="">Terms&nbsp;and&nbsp;Condition</a>
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