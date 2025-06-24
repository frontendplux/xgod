import preventAhrfFromClick from "./hrefCond.js";

export default async function router(path){
  const currentpath= localStorage.getItem('path');
  if(currentpath == path){
    history.replaceState({path:path},"",path);
  }
  else{
    history.pushState({path:path},"",path);
    localStorage.setItem('path',path);
  }
  document.getElementById('root').innerHTML=await loadData(path);
  preventAhrfFromClick();
}

function loadData(path){
    switch (path) {
        case '/':
            return 
            break;
    
        default:
            break;
    }
}