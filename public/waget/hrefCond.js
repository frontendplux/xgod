import router from "./router.js";

export default function preventAhrfFromClick(){
    document.querySelectorAll('a').forEach(element => {
        element.addEventListener('click',e=>{
           e.target.preventDefault();
           router(e.target.href);
        })
    });
}

[
    {
        icon:"giticon",
        text:"git hub open",
        link:"home"
    },
    {
        icon
    }
]