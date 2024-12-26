localforage.getItem('bp_user').then((user)=>{
	if(user){
		if(typeof user!=='object'){
            user = JSON.parse(user);
        }
		
		
		if(document.querySelector('.player-mask')){
		}else{
			const style = document.createElement("style");
		    style.setAttribute("id", 'player-mask');
		    style.innerHTML = `.plyr__video-wrapper:before {
			    content: '${user.displayname}';position: absolute;top: 0;left: 0;width: 100%;height: 100%;opacity: 0.6;font-style: italic;display: flex;z-index: 1;align-items: center;justify-content: center;text-indent: -4px;text-shadow: 1px 2px var(--shadow);font-size: 3vw;color: var(--text);font-weight: 900;
			    animation:bounce 20s linear 500;
			}@keyframes bounce{ 0%{ transform:translate(0px,-45%); } 25%{ transform:translate(-20vw,0vh); } 50%{ transform:translate(0vw,40%); } 75%{ transform:translate(20vw,0); } 100%{ transform:translate(0px,-45%); } }`;
			document.body.appendChild(style);
		}
		
	}
});