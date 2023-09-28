<!DOCTYPE html>
<?php

# VERSION 20230927

# vim info : set tabstop=4


require_once("config/autoload.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<!--<title>IP Camera Configure</title>-->
		<script type="module" src="websocket2.js"></script>
		<link rel="stylesheet" href="css/general.css"  />
	</head>
<body>


<?php
	if ($myCFG->get("hls_method"))
	{
		# HLS method
?>
	<script src="scripts/hls.js"></script>
	<hr/>
	<!--
		La partie ci dessous provient de : https://blog.eleven-labs.com/fr/video-live-dash-hls/
		le .js et .js.map aussi, donc si changement revoir ce site...

		le source est censé se trouver : https://github.com/video-dev/hls.js/
		mais nébuleux....

	-->
	<video id="video" controls width="<?php print $myCFG->get("vid_sz_width"); ?>"  controls autoplay>
		<source src="http://192.168.3.98:8082" type="video/mp4">
    <script>
	var video = document.getElementById('video');
	if (Hls.isSupported())
	{
		var hls = new Hls({
				            debug: true,
				          });
		hls.loadSource('<?php print $myCFG->get("hls_url_stream"); ?>');
		hls.attachMedia(video);
		hls.on(Hls.Events.MEDIA_ATTACHED, function () {
							            video.muted = true;
							            video.play();
							          });
	}
      // hls.js is not supported on platforms that do not have Media Source Extensions (MSE) enabled.
      // When the browser has built-in HLS support (check using `canPlayType`), we can provide an HLS manifest (i.e. .m3u8 URL) directly to the video element through the `src` property.
      // This is using the built-in support of the plain video element, without using hls.js.
	else if (video.canPlayType('application/vnd.apple.mpegurl'))
	{
		video.src = '<?php print $myCFG->get("hls_url_stream"); ?>';
		video.addEventListener('canplay', function () {
			video.play();
			});
	}
	</script>
	</video>
	<hr/>
<?php
	} # if for HLS
	elseif ( $myCFG->get("motion_method") )
	{
		# Motion method
?>
	<script isrc="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script isrc="https://cdn.jsdelivr.net/npm/net@1.0.2/index.min.js" crossorigin="anonymous"><!-- bon moteur de recherche https://www.jsdelivr.com/ --></script>
	<script isrc="https://cdn.jsdelivr.net/npm/websocket@1.0.34/%2Besm"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
	<script type="module" src="https://cdnjs.cloudflare.com/ajax/libs/systemjs/6.14.2/system.min.js"></script>
		<script type="module">

const	url_cam1			= "<?php print $myCFG->get("motion_cam1"); ?>";
const	url_cam1_err		= "<?php print $myCFG->get("motion_cam1_err"); ?>";

let List_Sock = [];
let Model_Sock = {
		sock:		undefined,
		nb:			0,
		created:	0,
		url:		"",
	}
let lvl						= 40; // Level_debug
let	sock					= undefined;
let NB						= 0;
let	status_error			= 0;
const Time_Fetch			= 1000;
const Time_isPort8081Exist	= 1400;
const Limit_number_process	= 0;	// number max process, 0 for 1, 1 for 2....

const imgs = document.querySelectorAll("img[id='img_mjpeg_noJS']");

if ( imgs !== null)
{
	for (const img of imgs) {
		  img.remove();
	}
	//imgs;
}

// Vérifie si le port 8082 est ouvert grace à ping_port.php toutes les (Time_isPort8081Exist / 1000) second !
if (1)
{
	window.onload = function()
	{
		ChargerFluxVideo();
		const isPort8081Exist	= setInterval(() =>
		{
    		// Vérifie si la variable sock est définie
			if ( lvl >= 90 ) console.log("SetInterval() start: ");
			const myObject	= {};
			const count		= List_Sock.length; // 0 == vide !

			// Je garde tjs Limit_number_process+1 élément(s) en attente avant d'en créer un nouveau.
			if (count <= Limit_number_process )
			{
				// Lancement d'un nouveau process fetch() pour vérifier le port du flux via ping_port.php
				// List_Sock est la liste d'attente.
				const Sock		= Object.create(Model_Sock);
				Sock.created	= Date.now();
				const obj		= Sock;
				Sock.url		= "http://192.168.3.98/surveillance/ping_port.php?nb="+NB+"&time="+Sock.created;
				Sock.nb	= NB;
				if ( lvl >= 90 ) console.log("SetInterval() ORDO: NB: '"+NB+"' obj.created='"+obj.created+" obj.created+1000="+(obj.created + Time_Fetch)+" List_Sock.lenght='"+List_Sock.length+"'"); 
				NB++;
				List_Sock[ count ]	= Sock;

				myFetch(Sock);
			}
			else
			{
				// La limite étant atteinte ( Limit_number_process ) on vérifie si la connexion au ping_port.php est validée.
				const c	= List_Sock.length;
				for(let i=0; i <= c; i++)
				{
					if ( List_Sock[i] !== undefined)
					{
						const date	= Date.now();
						const obj	= List_Sock[i];
						if ( date >= (obj.created + Time_Fetch) )
						{
							// Creation date passed
							if ( obj.sock === undefined )
							{
								// Si le process est "undefined" c'est soit fetch() n'a pas encore pu avoir de réponse,
								// soit la connexion a échouée. En effet quand fetch rencontre un problème réseau, il ne renvoie
								// rien est obj.sock n'est jamais initialisé et l'exécution du code javascript est interrompu...
								// Donc si ping_port.php renvoie le code HTTP 200 ou 501 fetch() initialise obj.sock
								// s'il ne peut pas ouvrir de connexion tcp/ip il génère une erreur JS et bloque l'exécution
								// de tout le reste du code JS !!! d'où le async à la fonction myFetch()

								// Ici le temps de réponse de fetch() étant dépassé, le process sera effacé après la chaine de IF.
								if ( lvl >= 40 ) console.log("SetInterval() TIEMOUT fetch() this process will be deleted: Process N°'"+i+" Date.now='"+date+"' obj["+i+"].created='"+obj.created+" obj.created+1000="+(obj.created + Time_Fetch)+" List_Sock.lenght='"+List_Sock.length+"'");
							}
							else
							{
								if (typeof obj.sock === "object")
								{
									// Vérifie si le port est ouvert
									if (obj.sock.status === 200)
									{
										if ( lvl >= 40 ) console.log("SetInterval(): CONGRULATION!!! fetch was able to open the video !");
										ChargerFluxVideo();
									}
									else
									{
										const img = document.querySelector("#img_mjpeg");
										const url	= "https://static-cse.canva.com/blob/604057/giphy3.gif";
										if (img.src !== url)
										{
											img.src	= url;
										}
										if ( lvl >= 40 ) console.log("SetInterval(): fetch() returns bad HTML code ");
										status_error++;
									}
								}
							}
							List_Sock.splice(i, 1);
						}
					}
				}
			}

		}, Time_isPort8081Exist);
	};
}

/*
	ChargerFluxVideo()
	3 Possibilités :
		1. On créé le tag img, s'il n'existe pas, pour faire afficher la vidéo.
		   <!> j'ai choisi de le placer par défault dans le code html, mais vous pouvez l'enlever.
		   Seulement, si votre navigateur a Javascript désactivé (ca existe encore ? :) rien ne fonctionnera.
		   - j'ai pas oublié lynx/links/wget....
		2. On change la src que si le status_error à changé depuis la dernière insertion du tag <img id="img_mjpeg" src=...
		   Ceci pour concourner le problème de cache, malgré qu'ils soient désactivés, du navigateur (chez moi Chrome).
		   En effet, malgré la désactivation des caches, si on remet juste <?php print $myCFG->get("motion_cam1"); ?> (donc même url) le navigateur
		   ne réactualise pas la lecture, il n'affiche que la dernière séquence lue...
		3. On ne fait rien.

 */
function ChargerFluxVideo() {
	let	state		= 0;
	const id		= "video_img";
	const new_Url	= url_cam1+"?status_error="+status_error;
	const img		= document.querySelector("#img_mjpeg");

	if ( lvl >= 99) console.log("ChargerFluxVideo():  newUrl: "+new_Url);

	if (img !== null)
			if ( lvl >= 99)
					console.log("ChargerFluxVideo(): img.src: "+img.src?img.src:" img == null");
	// n'existe pas
	if (img === null)	state |= 1;
	// existe mais src différente
	if (img !== null && img.src != new_Url) state	|= 2;
	if ( lvl >= 99) console.log("state="+state);
	if ( state === 1)
	{
		const div	= document.createElement("div");
		const img	= document.createElement("img");

		div.id		= id;
		img.id		= "img_mjpeg";

		img.src		= new_Url;
		if ( lvl >= 99) console.log("ChargerFluxVideo(): Creation tag <img...>");
		//img.width	= "<?php print $myCFG->get("vid_sz_width"); ?>";
		div.appendChild(img);

		img.onload	= function () {
			// La nouvelle source a été chargée avec succès
			const ancienneBaliseImg = document.getElementById( id );
			ancienneBaliseImg.parentNode.replaceChild(div, ancienneBaliseImg);
		};

		img.onerror = function () {
			// Erreur de chargement du flux vidéo
			img.src = url_cam1_err;
			status_error++;
		};
	}
	else if (state === 2)
	{
			img.src		= new_Url;
			if ( lvl >= 99) console.log("ChargerFluxVideo(): Modification <im src='...'");
	}
}

async function myFetch(obj)
{
	// Vérifie si le port est ouvert
	if (obj && (typeof obj === "object") )
	{
			const date	= Date.now();
		obj.sock	= await fetch( obj.url, {
			method: "GET",
			timeout: Time_Fetch,
		});
	}
	else
	{
		 if ( lvl >= 1 ) console.log("'myFtech( obj ), obj ne contient pas d'objet, erreur fatale!");
	}

	return( obj );
}
		</script>
	<div id="video_img">
		<div id="video_img_alert">
		</div>
<!--
-->
		<img id="img_mjpeg_noJS" src="<?php print $myCFG->get("motion_cam1"); ?>?status_error=0" />
	</div>
	<hr/>
	<script type="module" isrc="scripts/Net.js" crossorigin="anonymous"></script>
	<script>
		function isChromiumBased()
		{
			let ret		= 0;
			let cond	= true;
			// MAJ 20 juillet 2023, version 103.0.5060.114
			if ( window.navigator.userAgent.includes("Chrome/") )
				//Number.bitSet(ret, 2);
				ret	|= 2;
		  	if ( window.navigator.userAgent.includes("Chromium/") )
				//Number.bitSet(ret, 1);
				ret	|= 1;

			if ( ret == 0)
				cond = false;

			console.log("Chromium return="+ cond);
			return( cond );
		}

		if (!isChromiumBased()) {
			console.log("non compatible Chromium");
			const div = document.getElementById("video_img_alert");
			div.innerHTML = "Votre navigateur n'est pas de la famille 'Chromium'. Il est possible que le sequence vidéo mjpeg ne s'affiche pas...";
		}

	</script>
<?php
	}
	else	# webcam normal
	{
?>

	<div id="video_img">
		<video id="video" controls width="<?php print $myCFG->get("vid_sz_width"); ?>"  controls autoplay >
			<source src="<?php print $myCFG->get("camera1"); ?>"
		</video>
	</div>
	<hr/>
<?php
	}

?>
<div id="console">
</div>
</html>
</body>
