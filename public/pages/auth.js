import getDataFromServerPost from "../api/fetchdata.js";
import router from "../waget/router.js";

export async function  login(template){
    window.login=async e=>{
        e.innerHTML="Loading..."
        const user=document.getElementById('user').value.trim();
        const pass=document.getElementById('pass').value.trim();
        const err=document.getElementById('error');
        err.innerHTML="";
        if(user || pass == "") alert('enter all fields');
        else{
            const data=await getDataFromServerPost('server/auth',{type:"login",user:user,pass:pass});
            if(data.status == true) {
                localStorage.setItem('user',data.email);
                sessionStorage.setItem('sessionId',data._id);
                router('member');
            }
            else{
                err.innerHTML=data.message;
            }
        }
        e.innerHTML="Log In";
    }
    return template;
}

export async function  signup(template){
    window.signup=async e=>{
        e.innerHTML="Loading..."
        const email=document.getElementById('email').value.trim();
        const err=document.getElementById('error');
        err.innerHTML="";
        if(user == "") alert('Please provide a valid email address');
        else{
            const data=await getDataFromServerPost('server/auth',{type:"signup",email:email});
            if(data.status == true) {
                sessionStorage.setItem('user',data._id);
                router('/signup-user');
            }
            else{
                err.innerHTML=data.message;
            }
        }
        e.innerHTML="Sign Up";
    }
    return template
}

export async function  signup_user(template){
    sessionStorage.getItem('user') ? '' : router('/signup');
    window.verifyusername=async e=>{
        const verify=document.getElementById(e);
          verify.innerHTML="<span class='text-black'>loading...</span>";
        const data=await getDataFromServerPost('server/auth',{type:"signup-user-verify-actions",user:user});
        if(data.status == true) verify.innerHTML="<span class='text-green-600'>username is available.</span>";
        else verify.innerHTML="<span class='text-red-600'>username is taken.</span>";
    }

    window.signupUsername=async e=>{
        sessionStorage.getItem('user') ? '' : router('/signup');
        e.innerHTML="Loading..."
        const user=document.getElementById('user').value.trim();
        const err=document.getElementById('error');
        err.innerHTML="";
        if(user == "") alert('Please create a username');
        else{
            const data=await getDataFromServerPost('server/auth',
            {type:"signup-user",_id:sessionStorage.getItem('user'),user:user});
            if(data.status == true) {
                sessionStorage.setItem('user',data._id);
                router('/signup-password');
            }
            else{
                err.innerHTML=data.message;
            }
        }
        e.innerHTML="Verify Username";
    }
    return template;
}

export async function  signup_Password(){
    sessionStorage.getItem('user') ? '' : router('/signup');
    window.signupPass=async e=>{
        sessionStorage.getItem('user') ? '' : router('/signup');
        e.innerHTML="Loading..."
        const pass=document.getElementById('pass').value.trim();
        const err=document.getElementById('error');
        err.innerHTML="";
        if(/^[A-Z][a-zA-z0-9]{6,}$/.test(pass)){
            alert(`
                Please create a valid password
            `);
        }
        else{
            const data=await getDataFromServerPost('server/auth',
            {type:"signup-password",_id:sessionStorage.getItem('user'),pass:pass});
            if(data.status == true) {
                sessionStorage.setItem('user',data.email);
                router('/signup-confirm-passcode');
            }
            else{
                err.innerHTML=data.message;
            }
        }
        e.innerHTML="Create Password";
    }
    return template;
}


export async function  signup_Confirm_email(template){
    sessionStorage.getItem('user') ? '' : router('/signup');
    window.signupPasscode=async e=>{
        sessionStorage.getItem('user') ? '' : router('/signup');
        e.innerHTML="Loading..."
        const pass=document.getElementById('pass').value.trim();
        const err=document.getElementById('error');
        err.innerHTML="";
        if(pass == ''){
            alert(`
                Please Enter a valid passcode sent to "${sessionStorage.getItem('user')}";
            `);
        }
        else{
            const data=await getDataFromServerPost('server/auth',
            {type:"signup-confirm-email",email:sessionStorage.getItem('user'),passcode:pass});
            if(data.status == true) {
                sessionStorage.removeItem('user');
                localStorage.setItem('user',data.email);
                sessionStorage.setItem('sessionId',data._id);
                router('/member');
            }
            else if(data.status == false){
                err.innerHTML=data.message;
            }
            else{
                sessionStorage.removeItem('user');
                router("/signup");
            }
        }
        e.innerHTML="Confirm Passcode";
    }
    return template;
}
