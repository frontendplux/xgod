import {getDataFromServerGet} from "../api/fetchdata.js";

//---------------------- index 1
export async function getVideoData(url){
    const blog=await getDataFromServerGet(url)
    const blogPost=blog.forEach(element => {
        return/*html*/`
        <div>
            <div>${element.video}</div>
            <div>${element.title}</div>
            <div>${element.desc}</div>
            <div>${element.time}</div>
            <div>
                ${element.views} | ${element.comments}| ${element.reaction}
            </div>
        </div>
        `
    }).join("");
    return/*html*/`
    <div>
        ${blogPost}
    </div>
    `;
}

// ----------------------index 2


export async function  getVideoData(){

}

