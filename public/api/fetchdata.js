export default async function getDataFromServerPost(url,data){
   const req=await fetch(url,{method:"post",body:JSON.stringify(data)});
   return req.json();
}

export default async function getDataFromServerGet(url){
    const req=await fetch(url);
    return req.json();
 }