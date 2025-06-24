import authUser from "../api/auth.js";
import {getDataFromServerPost, getDataFromServerGet} from "../api/fetchdata.js";

//---------------------- private  
export async function getProfileDataPublic(url){
    const user=await getDataFromServerGet(url)
        return/*html*/`
        <div>
            <div>${user.image}</div>
            <div>${blog.title}</div>
            <div>${element.desc}</div>
            <div>${element.time}</div>
            <div>
               ${element.user-image} | 
               ${element.username} | 
               ${element.views} |
               ${element.comments} |
               ${element.share}
               ${element.reaction}
            </div>
        </div>
    `
}

//---------------------- private  
export async function getProfileDataPrivate(url){
    const user=await authUser();
    if (user.status == true){
     const  postData=await getDataFromServerPost(url,user.email);
        return/*html*/`
        <div>
            <div>${user.image}</div>
            <div>${blog.title}</div>
            <div>${element.desc}</div>
            <div>${element.time}</div>
            <div>
               ${element.user-image} | 
               ${element.username} | 
               ${element.views} |
               ${element.comments} |
               ${element.share}
               ${element.reaction}
            </div>
        </div>
        `;
    }else{
        router
    }
}


