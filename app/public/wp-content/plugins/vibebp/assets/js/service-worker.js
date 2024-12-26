importScripts("[PLUGIN_URL]assets/js/localforage.min.js");
var localForage = localforage;
[FIREBASE]
importScripts("[PLUGIN_URL]assets/js/firebase-app.js");
importScripts("[PLUGIN_URL]assets/js/firebase-messaging.js");	
firebase.initializeApp([FIREBASE_OBJECT]);
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
	var notificationTitle = "Message Title";
	var notificationOptions = {
		body: "Message body.",
		icon: "/firebase-logo.png"
	};
	return self.registration.showNotification(notificationTitle,
	notificationOptions);
});
[/FIREBASE]
const OFFLINE_VERSION = [SW_VERSION];
const CACHE_FIRST = [CACHE_FIRST];
const CACHE_NAME = 'vibebp_'+[SW_VERSION];
const OFFLINE_URL = [OFFLINE_URL];
const DEFAULT_IMAGE=[DEFAULT_IMAGE];
let static_cache_assets = [STATIC_ASSETS];
self.addEventListener("install", event => {
  	console.log("Attempting to install service worker and cache static assets",event);
  	event.waitUntil(
    caches.open(CACHE_NAME)
    	.then(cache => {
    		
    		static_cache_assets.push(OFFLINE_URL);
    		static_cache_assets.push(DEFAULT_IMAGE);
    		static_cache_assets.push("./");
    		static_cache_assets.map(function(cache_url){	    			
    			const request = new Request(cache_url, {mode: "no-cors"});
    			fetch(request).then(response => cache.put(request, response));
			})
	    })
  	);
});



self.addEventListener("activate", event => {
	caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.filter(function(cacheName) {
          if(cacheName.indexOf("vibebp") == 0 && cacheName != CACHE_NAME){
          	return true
          }
        }).map(function(cacheName) {
          return caches.delete(cacheName);
        })
      );
    })
});
self.addEventListener("fetch", async(event) => {

	let CACHE_REQUEST = CACHE_FIRST;
	let CACHENAME = CACHE_NAME;
	localforage = localForage;
	let currDate = Math.round(new Date().getTime()/1000);

	if (!event.request.url.startsWith(self.location.origin)) {
	   return;
	}

	if (event.request.url.indexOf('browser-sync') > -1) {return;}
	if (/wp-admin/.test(event.request.url)){return;}
	if (/wp-login/.test(event.request.url)){return;}
	//if (/\?ver\=/.test(event.request.url)){return;}
	if (event.request.url === (self.location.origin + '/')) {return;}
	if (/\?nocache/.test(event.request.url)){return;}
	if (/\&nocache/.test(event.request.url)){return;}
	if (/\?no_cache/.test(event.request.url)){return;}
	if (/\&no_cache/.test(event.request.url)){return;}
	if (/\?upload/.test(event.request.url)){return;}
	if (/\&upload/.test(event.request.url)){return;}
	
  	if ((/wp-json\/wplms/.test(event.request.url) || /wp-json\/vibe/.test(event.request.url) || /wp-json\/vbp/.test(event.request.url)) && event.request.destination != 'document'){
  		if(event.request.url.indexOf('nocache') > -1){
  			return;
  		}
  		let cloned = event.request.clone();
  		
  		if(event.request.method === "POST" || event.request.method === "GET"){
  			if (/force/.test(event.request.url)){
				CACHE_REQUEST = false;
			}
			if (/xapi/.test(event.request.url)){
				CACHE_REQUEST = false;
			}
			if (/\?post/.test(event.request.url) || /&post/.test(event.request.url)){
				event.respondWith((async () => {
					
					let newdata = {url:event.request.url,headers:cloned.headers};
					let postdata = await cloned.json();
					newdata['data'] = postdata;
					let post_data = await localforage.getItem('post_data');
					if(post_data){
						post_data = JSON.parse(post_data);
					}
					if(!Array.isArray(post_data)){
						post_data=[];
					}
					return fetch(newdata.url,{
						method:'post',
						headers:newdata.headers,
					    body:JSON.stringify(newdata.data),
					}).then(async(response) => {
					
	  					if(response.ok){
	  						if(post_data.length){
	  							let cdata = newdata.data;
	  							if(typeof cdata === 'object' && cdata.hasOwnProperty('token')){
		  							cdata.token='';
		  						}
  								let index = post_data.findIndex((pd)=>JSON.stringify(pd.data)===JSON.stringify(cdata) && pd.url == newdata.url);
	  							if(index > -1){
	  								post_data.splice(index,1);
	  								await localforage.setItem('post_data',JSON.stringify(post_data));
	  							}
	  						}
	  						return response.clone().json().then((rs)=>{
		  						return new Response(JSON.stringify(rs),  { "status" : 200 , "statusText" : "ok!from service worker" })
		  						
	  						});
	  					}else{
	  						if(typeof newdata.data === 'object' && newdata.data.hasOwnProperty('token')){
	  							newdata.data.token='';
	  						}
	  						if(post_data.length){
  								let index = post_data.findIndex((pd)=>JSON.stringify(pd.data)===JSON.stringify(newdata.data) && pd.url == newdata.url);
	  							if(index < 0){
	  								post_data.push(newdata);
	  							}
	  						}else{
	  							post_data.push(newdata);
	  						}
							await localforage.setItem('post_data',JSON.stringify(post_data));
							return new Response(JSON.stringify({status:0,message:'Server Offline! We saved data and we will retry once the connection resumes.'}), { "status" : 200 , "statusText" : "ok!" });	
	  					}
					}).catch(async(err)=>{
						console.log(err);
						if(typeof newdata.data === 'object' && newdata.data.hasOwnProperty('token')){
  							newdata.data.token='';
  						}
  						if(post_data.length){
  							let index = post_data.findIndex((pd)=>JSON.stringify(pd.data)===JSON.stringify(newdata.data) && pd.url == newdata.url);
  							if(index < 0){
  								post_data.push(newdata);
  							}
  						}else{
  							post_data.push(newdata);
  						}
						await localforage.setItem('post_data',JSON.stringify(post_data));
						return new Response(JSON.stringify({status:0,message:'Server Offline! We saved data and we will retry once the connection resumes.'}), { "status" : 200 , "statusText" : "ok!" });
		    		})
		    	})());
   				
			}else{


				event.respondWith((async () => {
					let userStaleRequests = localforage.createInstance({
			            name: "vibebp_stale_requests",
			            storeName:'user'
			        });
			        let globalStaleRequests = localforage.createInstance({
			            name: "vibebp_stale_requests",
			            storeName:'global'
			        });

			        let checkNames = await localForage.getItem('vibebpstoreItem');
					if(checkNames){
						CACHENAME = checkNames.cache_name;
						localforage = localforage.createInstance({
							  	name: checkNames.cache_name,
						});
						CACHE_REQUEST = false;
					}

			        let url = event.request.url;
                    url = url.replaceAll("?force","");
                    url = url.replaceAll("&force","");
			        let cachedResponse = await localforage.getItem(url);
                    if(!cachedResponse){
                        cachedResponse = await localforage.getItem(event.request.url);
                    }
                    let user_stale_requests = [];
                    let global_stale_requests = [];

                    if(cachedResponse){

                        user_stale_requests = await userStaleRequests.keys();
                        global_stale_requests = await globalStaleRequests.keys();

                        try{
                            cachedResponse = JSON.parse(cachedResponse);

                            //use cases
                            //const keywords = ['quizzes','courses','data.groups'];
                            if(global_stale_requests.length){
                            	
                                for (let i = global_stale_requests.length; i >= 0; i--) {
                                	if(typeof global_stale_requests[i]!=='undefined'){
                                		
	                                    let actual_keyword = global_stale_requests[i];
	                                    let keyword = actual_keyword;
	                                   	keyword = keyword.split('|');
	                                   	
	                                   	if(typeof keyword[1]!=='undefined'){
	                                   		keyword = keyword[1];
	                                   		if(cachedResponse.hasOwnProperty(keyword) > -1 && Array.isArray(cachedResponse[keyword])){
		                                        let data = await globalStaleRequests.getItem(actual_keyword);
		                                        for(let i in data){
		                                            let index = cachedResponse[keyword].findIndex(data_item=>parseInt(data_item[data[i].property]) === parseInt(i));
		                                            if(index > -1 && parseInt(cachedResponse['vibebp_timestamp']) < parseInt(data[i].time)){
		                                                CACHE_REQUEST = false;
		                                                break; break;
		                                            }
		                                        }
		                                    }
	                                   	}
                                	}
                                	
                                    
                                }
                            }
                        }catch(err){}
                    }
					let user_stale_index = -1;

					if(CACHE_REQUEST && user_stale_requests && user_stale_requests.length){
						let i= user_stale_requests.findIndex(stale_request =>  event.request.url.indexOf(stale_request) > -1);
						if(i > -1){//chage the logic to match timestamp
							if(cachedResponse){
								let staleRequestTimestamp = await userStaleRequests.getItem(user_stale_requests[i]);
								if(cachedResponse.hasOwnProperty('vibebp_timestamp') && cachedResponse.vibebp_timestamp && parseInt(cachedResponse.vibebp_timestamp) <  parseInt(staleRequestTimestamp)){
									user_stale_index = user_stale_requests[i];
									CACHE_REQUEST = false;	
								}
								
							}
							
						}
					}
					
					
					let global_stale_index = -1;

					if(CACHE_REQUEST && global_stale_requests && global_stale_requests.length){
						let i= global_stale_requests.findIndex(stale_request =>  event.request.url.indexOf(stale_request) > -1);

						if(i > -1){//chage the logic to match timestamp
							
							if(cachedResponse){
								let staleRequestTimestamp = await globalStaleRequests.getItem(global_stale_requests[i]);
								if(cachedResponse.hasOwnProperty('vibebp_timestamp') && cachedResponse.vibebp_timestamp && parseInt(cachedResponse.vibebp_timestamp) <  parseInt(staleRequestTimestamp)){
									global_stale_index = global_stale_requests[i];
									CACHE_REQUEST = false;	
								}
							}
						}
					}

					if(CACHE_REQUEST){
						if(cachedResponse){
							return new Response(JSON.stringify(cachedResponse), { "status" : 200 , "statusText" : "ok!" });	
						}
			          	return localforage.getItem(event.request.url).then((res)=>{
			          		if(res){
			          			return new Response(res, { "status" : 200 , "statusText" : "ok!" });		
			          		}else{
			          			return fetch(cloned).then(res=>res.clone().json())
							  				.then(response => {
							  					response['vibebp_timestamp'] = currDate;
                                                let url = cloned.url;
                                                url = url.replaceAll("?force","");
                                                url = url.replaceAll("&force","");
							  					return localforage.setItem(url,JSON.stringify(response)).then(()=>{
							  						return new Response(JSON.stringify(response),  { "status" : 200 , "statusText" : "ok!" })
						  						})
											}).catch(()=>{
												return new Response(JSON.stringify({status:0,message:'Unable to fetch'}));
											});
			          		}
						})
	          		}


                    if(!CACHE_REQUEST){
						return fetch(cloned).then(res=>res.clone().json())
		  				.then(response => {
		  					response['vibebp_timestamp'] = currDate;
                            let url = cloned.url;
                            url = url.replaceAll("?force","");
                            url = url.replaceAll("&force","");
							return localforage.setItem(url,JSON.stringify(response)).then(()=>{

									return new Response(JSON.stringify(response),  { "status" : 200 , "statusText" : "ok!" })
							});
						}).catch(()=>{
							if(cachedResponse){
								if(user_stale_index !== -1 || global_stale_index !== -1){
									cachedResponse = {...cachedResponse,message:'You are viewing stale data!'};
								}
								return new Response(JSON.stringify(cachedResponse), { "status" : 200 , "statusText" : "ok!" });	
							}
							return localforage.getItem(event.request.url).then((res)=>{
								if(user_stale_index !== -1 || global_stale_index !== -1){
									res = {...res,message:'You are viewing stale data!'};
								}
								return new Response(res, { "status" : 200 , "statusText" : "ok!" });	
							})
			    		})
					}
				})());
			}

			let lf = localforage.createInstance({
			  	name: "vibebp_last_requests",
			});
			try{
				lf.setItem(cloned.url,JSON.stringify({method:'POST'}));
			}
			catch(err){
				lf.setItem(cloned.url,'');
			}
	  	}

	}else{

  		
  		if(/wp-content\/plugins/.test(event.request.url) || 
  			/wp-includes/.test(event.request.url) || /wp-content\/uploads/.test(event.request.url)){
		  	
		  	switch (event.request.destination) {
			    case 'style':
			    case 'script':
			    case 'image':
			    case 'font':
			    	if(CACHE_REQUEST){
			    			
			    			if (event.request.headers.get('range')) {
							    var pos =
							    Number(/^bytes\=(\d+)\-$/g.exec(event.request.headers.get('range'))[1]);
							    console.log('Range request for', event.request.url,
							      ', starting position:', pos);
							    event.respondWith(async()=>{
							    	let checkNames = await localForage.getItem('vibebpstoreItem');
									if(checkNames){
										CACHENAME = checkNames.cache_name;
										localforage = localforage.createInstance({
											  	name: checkNames.cache_name,
										});
									}

							      caches.open(CACHENAME)
							      .then(function(cache) {
							        return cache.match(event.request.url);
							      }).then(function(res) {
							        if (!res) {
							          return fetch(event.request)
							          .then(res => {
							            return res.arrayBuffer();
							          });
							        }
							        return res.arrayBuffer();
							      }).then(function(ab) {
							        return new Response(
							          ab.slice(pos),
							          {
							            status: 206,
							            statusText: 'Partial Content',
							            headers: [
							              ['Content-Range', 'bytes ' + pos + '-' +
							                (ab.byteLength - 1) + '/' + ab.byteLength]]
							          });
							      })
							    })();
							  }else{

					        		event.respondWith((async () => {
						          		let checkNames = await localForage.getItem('vibebpstoreItem');
										if(checkNames){
											CACHENAME = checkNames.cache_name;
											localforage = localforage.createInstance({
												  	name: checkNames.cache_name,
											});
											CACHE_REQUEST = false;
										}

						          		const cache = await caches.open(CACHENAME);
						          		const cachedResponse = await cache.match(event.request);
				          			

							          	if(CACHE_REQUEST){
							          		if (cachedResponse) {
								            	return cachedResponse;
							        		}
						        		}

						          		const response = await fetch(event.request).catch(()=>{
							          		if (cachedResponse) {
									            return cachedResponse;
								        	}
							          	});

						          		if (!response || response.status !== 200 || response.type !== 'basic') {
							            	return response;
						          		}
							          	const responseToCache = response.clone();
							          	await cache.put(event.request, response.clone());

							          	return response;	          
						          
						        	})());
			        	}
			    	}else{
			    		event.respondWith(
				    		fetch(event.request).catch(()=>{
				    			return caches.match(event.request)
				    		})
	       			);
			    	}
			    	
			    break;
		    }
		}

		if(event.request.destination == 'document'){
			
			event.respondWith((async () => {
				let checkNames = await localForage.getItem('vibebpstoreItem');
				if(checkNames){
					CACHENAME = checkNames.cache_name;
					localforage = localforage.createInstance({
						  	name: checkNames.cache_name,
					});
				}
		      	try {
		        // First, try to use the navigation preload response if it's supported.
		        	const preloadResponse = await event.preloadResponse;
		        	if (preloadResponse) {
		          		return preloadResponse;
		        	}

		        	const networkResponse = await fetch(event.request);
		        	return networkResponse;
		      	} catch (error) {
			        const cache = await caches.open(CACHENAME);
		        	const cachedResponse = await cache.match(OFFLINE_URL);
		        	return cachedResponse;
		      	}
		    })());
		}else if (event.request.mode === 'navigate') {
			
            event.respondWith((async () => {
              try {
                const preloadResp = await event.preloadResponse;
        
                if (preloadResp) {
                  return preloadResp;
                }
        
                const networkResp = await fetch(event.request);
                return networkResp;
              } catch (error) {
        
                const cache = await caches.open(CACHENAME);
                const cachedResp = await cache.match(OFFLINE_URL);
                return cachedResp;
              }
            })());
        }
  	}
});
self.addEventListener("onsync", event => {
	console.log("onSync");
});
self.addEventListener("onmessage", event => {
	console.log("Message");
});
self.addEventListener("onpush", event => {
	console.log("Push");
});

self.addEventListener('periodicsync', function(event) {
  if (event.tag == 'vibebp-post-data') {
    event.waitUntil(checkPostData());
  }
});

const checkPostData = async() =>{
	let post_data = await localforage.getItem('post_data');
	if(post_data){
		post_data = JSON.parse(post_data);
		console.log(post_data);
		let uncompletedcalls = [];
		if(post_data && post_data.length){
			for (var i = 0; i < post_data.length; i++) {
				let data = post_data[i].data;
				if(data.hasOwnProperty('token')){
					data.token = await localforage.getItem('bp_login_token');
				}
				await fetch(post_data[i].url+'&nocache',{
					method:'post',
					headers:post_data[i].headers,
				    body:JSON.stringify(data),
				}).then(async(response) => {
					if(!response.ok){
						uncompletedcalls.push(post_data[i]);
					}
				}).catch((err)=>{
					console.log(err);
					uncompletedcalls.push(post_data[i]);
				});
			}
			
			console.log(uncompletedcalls);

			//logic for ghost api hits that does not completes ever , we should remove them
			if(uncompletedcalls.length){
				for (var i = uncompletedcalls.length - 1; i >= 0; i--) {
					if(!uncompletedcalls[i].hasOwnProperty('count')){
						uncompletedcalls[i].count=0;
					}
					uncompletedcalls[i].count++;
					if(uncompletedcalls[i].count > 10){
						uncompletedcalls.splice(i,1);
					}
				}
			}
			await localforage.setItem('post_data',JSON.stringify(uncompletedcalls));
		}
	}
}
