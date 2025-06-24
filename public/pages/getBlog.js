import {getDataFromServerPost, getDataFromServerGet} from "../api/fetchdata.js";

//---------------------- index 1
export async function getBlogData(url,template){
    const blog=await getDataFromServerGet(url)
    const blogPost=blog.forEach(element => {
        return
        // <div>
        //     <div>${element.image}</div>
        //     <div>${element.title}</div>
        //     <div>${element.desc}</div>
        //     <div>${element.time}</div>
        //     <div>
        //        ${element.user-image} | 
        //        ${element.username} | 
        //        ${element.views} |
        //        ${element.comments} |
        //        ${element.share}
        //        ${element.reaction}
        //     </div>
        // </div>
    }).join("");

    return/*html*/`
    <div>
        ${blogPost}
    </div>
    `;
}


