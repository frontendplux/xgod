import getDataFromServerPost from "./fetchdata.js";

export default async function authUser(){
    const data={
        type:"auth",
        user:localStorage.getItem('user'),
        sessionId:sessionStorage.getItem('sessionId')
    }
    const userData=await getDataFromServerPost('/server/auth',data);
    return userData;
}