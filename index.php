<?php

#require_once("config/config.php");
require_once("config/autoload.php");


			if ( isset($_SERVER) && isset($_SERVER["SERVER_ADDR"]) )
			{
				# Lancement depuis un serveur HTTPD
				$ip_this_page	= $_SERVER["SERVER_ADDR"];
				#$URL		= "http://".$ip_this_page."/".$PROJET_NAME;
				$URL		= $myCFG->get('url');
			}
			else
			{
				# Donc lancement par shell
				$ip_this_page		= "";
				$URL		= "file://.".$ip_this_page;
			}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Apache2 Debian Default Page: It works</title>
	<link rel="stylesheet" href="css/general.css" media="all" id="general" />
	<link rel="stylesheet" href="css/mosaic.css?1694597012" media="screen" id="mosaic" />
	<style type="text/css" media="all">
.videomp4 {
	width:	480px;

}
		</style>
    <style type="text/css" media="screen">
    </style>
  </head>
  <body>
    <div class="main_page">
      <div class="page_header floating_element">
        <img src="icons/gplv3-with-text-136x68.png" alt="Debian Logo" class="floating_element"/>
        <span class="floating_element">
Tools to manage Motion<br>
 video streams
        </span>
      </div>
      <div class="table_of_contents floating_element">
        <div class="section_header section_header_grey">
          TABLE OF CONTENTS
        </div>
        <div class="table_of_contents_item floating_element">
          <a href="#motd">About</a>
        </div>
        <div class="table_of_contents_item floating_element">
          <a href="#details">Technical details</a>
        </div>
<!--
        <div class="table_of_contents_item floating_element">
          <a href="#scope">Scope</a>
        </div>
        <div class="table_of_contents_item floating_element">
          <a href="#files">Config files</a>
        </div>
  -->
      </div>

      <div class="content_section floating_element">


        <div class="section_header section_header_red">
          <div id="about"></div>
          About
        </div>

        <div class="content_section_text">

<div id="motd">
<div id="fr" style="block">
<section lang="fr">

	<h1 onclick="toggleStyle('div_h1')">Gestion des séquences vidéos capturées par une camera</h1>
	<div id="div_h1" style="display: block">
		<p onclick="toggleStyle('div_h1')"><mark>cliquez sur le titre pour afficher le détail...</mark></p>
	</div><!-- div div_h2.1 -->
	<div id="div_h1" style="display: none">
		<p>Ce projet vous est proposé pour gérer les intrusions détectées par une caméra IP<p>
		<p>Il est destiné à ceux qui ne souhaitent pas utiliser le software livré avec leur caméra ou dont celui-ci est obsolette
comme c'est le cas pour la mienne où FlashPlayer est employé</p>
	</div><!-- div div_h1 -->

	<h2 onclick="toggleStyle('div_h2.1')">Sources des vidéos</h2>
	<div id="div_h2.1" style="display: block">
		<p onclick="toggleStyle('div_h2.1')"><mark>cliquez sur le titre pour afficher le détail...</mark></p>
	</div><!-- div div_h2.1 -->
	<div id="div_h2.1" style="display: none">
		<p>Les séquences sont capturées par Motion v4.3.2<p>
		<ul>
			<li>Elles sont traitées par le script detect.sh et video2image.php qui est lancé via Motion lors de la détection des mouvements</li>
			<ul>
			<li>on_movie_start /var/www/html/surveillance/bin/detect.sh -- MovieStart file=&quot;%f&quot; date=&quot;%Y%m%d&quot; heure=&quot;%H:%M:%S&quot; frame=&quot;%q&quot; event=%v</li>
			<li>on_movie_end /var/www/html/surveillance/bin/detect.sh -- MovieEnd file=&quot;%f&quot; date=&quot;%Y%m%d&quot; heure=&quot;%H:%M:%S&quot; frame=&quot;%q&quot; event=%v</li>
			</ul>
			<li>Ces scripts interviennent sur les vidéos que produit Motion</li>
			<ul>
				<li>Envoie d'email pour signaler une détection de mouvement depuis la caméra.</li>
				<li>extrait les informations et les stock dans un fichier json et XML</li>
					<ul>
						<li>durée</li>
						<li>taile de la vidéo</li>
						<li>nombre de frame</li>
					</ul>
				<li>la première image servant d'imagette (thumb)</li>
			</ul>
		</ul>
	</div><!-- div div_h2 -->

	<h2 onclick="toggleStyle('div_h2.2')">Warpper, middleware</h2>
	<div id="div_h2.2" style="display: block">
		<p onclick="toggleStyle('div_h2.2')"><mark>cliquez sur le titre pour afficher le détail...</mark></p>
	</div><!-- div div_h22 -->
	<div id="div_h2.2" style="display: none">
		<p>Ma caméra est compatible ONVIF PTZ Control service, mais son interface est obsolette, elle ne fonctionne qu'avec FlashPlayer, j'ai donc ajouté deux méthodes pour accéder à la surveillance à distance et en dehors de mon réseaui local<p>
		<h3>Méthode 1</h3>
		<ul>
			<li>Conection en utilisant le routage et redirection de port.</li>
			<ul>
				<li>Cette méthode permet d'avoir un controle des mouvements de la caméra ainsi qu'un accès directe à la caméra. Donc un contrôle total</li>
				<li>J'utilise une application sur smartphone compatible ONVIF, elle se nomme IP Cam Viewer Lite et la gestion de la caméra se fait sans aucune latence autre que la qualité du réseau !</li>
			</ul>
		</ul>
		<h3>Méthode 2</h3>
		<ul>
			<li>Usage d'un serveur RTMP/HLS</li>
			<ul>
				<li>Cette méthode permet d'avoir une vue de la caméra depuis une page html mais ceci sans avoir de controle de ses mouvements.</li>
				<li>Cepedant cette méthode n'est pas concluante sur mes 2 raspberry 2B et 4B, puisqu'il y une latence moyenne de de 20s sur les deux appareils.</li>
				<li>Il est bien entendu possible de l'associer à la méthode 1 pour avoir le control du mouvement!</li>
				<li>Deux pages complémente cette méthode. Elles permettent le controle des mouvements et le visionnage de la caméra.</li>
				<ul>
					<li><a href="camera_control.php">camera_control.php</a> qui utilise l'interface html/js de contrôle intégrée dans le firmware de la caméra</li>
					<li><a href="camera.php">camera.php</a> qui est chargé dans une frame et permet de visionner le flux vidéo généré par le serveur RTMP/HLS. Ceci reproduisant l'interface originalle en FlashPlayer que fournit le fabricant avec la caméra.</li>
				</ul>
			</ul>
		<p>Il est donc possible de remplacer l'interface de ma caméra IP qui est devenu inutilisable</p>
		<p>Pour vous aider, deux exemples de configuration NGINX se trouvent dans le dossier docs/nginx avec les commentaires utiles</p>
		</ul>
	</div><!-- div div_h22 -->
</section>
</div><!-- div "fr"-->


<section lang="en">
<h1 onclick="toggleStyle('div_h1')">Management of Video Sequences Captured by an IP Camera</h1>
	<div id="div_h1" style="display: block">
		<p onclick="toggleStyle('div_h1')"><mark>click on the title to view the details...</mark></p>
	</div><!-- div div_h1 -->
	<div id="div_h1" style="display: none">
		<p>This project is offered to you for managing intrusions detected by an IP camera.</p>
		<p>It is intended for those who do not wish to use the software provided with their camera or whose software is obsolete, as is the case with mine where FlashPlayer is used.</p>
	</div><!-- div div_h1 -->

<h2 onclick="toggleStyle('div_h2.1')">Video Sources</h2>
	<div id="div_h2.1" style="display: block">
		<p onclick="toggleStyle('div_h2.1')"><mark>click on the title to view the details...</mark></p>
	</div><!-- div div_h2.1 -->
	<div id="div_h2.1" style="display: none">
		<p>The sequences are captured by Motion v4.3.2.</p>
		<ul>
    		<li>They are processed by the detect.sh script and video2image.php, which is launched via Motion when motion is detected.</li>
    		<ul>
				<li>on_movie_start /var/www/html/surveillance/bin/detect.sh -- MovieStart file=&quot;%f&quot; date=&quot;%Y%m%d&quot; heure=&quot;%H:%M:%S&quot; frame=&quot;%q&quot; event=%v</li>
				<li>on_movie_end /var/www/html/surveillance/bin/detect.sh -- MovieEnd file=&quot;%f&quot; date=&quot;%Y%m%d&quot; heure=&quot;%H:%M:%S&quot; frame=&quot;%q&quot; event=%v</li>
    		</ul>
    		<li>These scripts intervene on the videos produced by Motion.</li>
    		<ul>
        		<li>Sending emails to report motion detection from the camera.</li>
        		<li>Extracting information and storing it in a JSON and XML file.</li>
            		<ul>
                		<li>Duration</li>
                		<li>Video size</li>
                		<li>Number of frames</li>
            		</ul>
        		<li>The first image serving as a thumbnail (thumb).</li>
    		</ul>
		</ul>
	</div><!-- div div_h2.1 -->

<h2 onclick="toggleStyle('div_h2.2')">Wrapper, Middleware</h2>
	<div id="div_h2.2" style="display: block">
		<p onclick="toggleStyle('div_h2.2')"><mark>click on the title to view the details...</mark></p>
	</div><!-- div div_h2.2 -->
	<div id="div_h2.2" style="display: none">
		<p>My camera is compatible with the ONVIF PTZ Control service, but its interface is obsolete; it only works with FlashPlayer. Therefore, I have added two methods to access remote surveillance both inside and outside my local network.</p>
		<h3>Method 1</h3>
		<ul>
    		<li>Connection using routing and port forwarding.</li>
    		<ul>
        		<li>This method allows for control of the camera's movements and direct access to the camera, thus full control.</li>
        		<li>I use a smartphone application compatible with ONVIF called IP Cam Viewer Lite, and camera management is done with no latency other than network quality.</li>
    		</ul>
		</ul>
		<h3>Method 2</h3>
		<ul>
    		<li>Use of an RTMP/HLS server.</li>
    		<ul>
        		<li>This method provides a view of the camera from an HTML page but without control of its movements.</li>
        		<li>However, this method is not conclusive on my Raspberry Pi 2B and 4B since there is an average latency of 20 seconds on both devices.</li>
        		<li>It is, of course, possible to combine it with Method 1 for motion control.</li>
        		<li>Two pages complement this method. They allow for motion control and camera viewing.</li>
        		<ul>
            		<li><a href="camera_control.php">camera_control.php</a> which uses the HTML/JS control interface integrated into the camera's firmware.</li>
            		<li><a href="camera.php">camera.php</a> which is loaded into a frame and allows for viewing the video stream generated by the RTMP/HLS server. This reproduces the original FlashPlayer interface provided by the manufacturer with the camera.</li>
        		</ul>
    		</ul>
		<p>It is therefore possible to replace the interface of my IP camera, which has become unusable.</p>
		<p>To assist you, two examples of NGINX configuration are located in the docs/nginx folder with useful comments.</p>
		</ul>
	</div><!-- div div_h2.2 -->

</section>

        </div><!-- div class="content_section_text" -->

        <div class="section_header">
          <div id="details"></div>
                Technical details
        </div>
        <div class="content_section_text">
          <p>
		  <ul>
		      <li>Page final, comportant le control de la webcam et affichant l'image avec &quot;Motion&quot;</li>
		          <ul>
				  <li><a href="<?php print $URL;?>/camera_control.php"><?php print $URL;?>/camera_control.php</a></li>
		          </ul>
			  <li>Gestion des mouvement détectés par Motion (MOSAIC): <a href="<?php print $myCFG->get('url_mosaic');?>"><?php print basename($myCFG->get('url_mosaic'));?></a></li>

			  <li>Motion solution</li>
			  <ul>
			  <li>Panneau de control Motion: <a href="http://<?php print $ip_this_page;?>:8081/"><?php print $ip_this_page;?>:8081/</a></li>
			  <li>Video via HTTP/1.1 accessible par VLC: <a href="http://<?php print $ip_this_page;?>:8082/"><?php print $ip_this_page;?>:8082/</a></li>
			  </ul>

			  <li>HLS solution </li>
					  <ul>
					  <li>Stream Dash : <a href="<?php print $URL;?>/stream/dash/stream0.mpd"><?php print $URL;?>/stream/dash/stream0.mpd</a></li>
					  <li>Stream Hls : <a href="<?php print $URL;?>/stream/hls/stream0.m3u8"><?php print $URL;?>/stream/hls/stream0.m3u8</a></li>
					  </ul>
			  <ul>
			  	<li>RTSP Vidéo format 1: <a href="rstp://<?php print $myCFG->get('camera_ip');?>/1">rtsp://<?php print $myCFG->get('camera_ip');?>/1</a></li>
			  	<li>RTSP Vidéo format 2: <a href="rstp://<?php print $myCFG->get('camera_ip');?>/2">rtsp://<?php print $myCFG->get('camera_ip');?>/2</a></li>
			  </ul>
			  <li>Statitique de convertion RTSP en RTMP.</li>
			  <ul><li>La caméra envoie le flux sur le serveur NGINX au format RTMP://</li>
			  <li>Lien du xsl des stats <a href="http://<?php print $myCFG->get('server_ip');?>:8083/stat">STATs</a></li>
			  </ul>

			  <li>WEBCAM: accès à la webcam depuis votre serveur HTTPd ! <a href="http://<?php print $ip_this_page;?>/camfull/"><?php print $ip_this_page;?>/camfull/</a></li>
				<ul>
					<li>
						Utile seulement si votre webcam n'est pas accessible depuis l'exterieur !
					</li>
				</ul>

		  </ul>

          </p>
          <p>
          </p>
        </div>

<!--
        <div class="section_header">
            <div id="docroot"></div>
                Document Roots
        </div>

        <div class="content_section_text">
            <p>
                By default, Debian does not allow access through the web browser to
                <em>any</em> file apart of those located in <tt>/var/www</tt>,
                <a href="http://httpd.apache.org/docs/2.4/mod/mod_userdir.html" rel="nofollow">public_html</a>
                directories (when enabled) and <tt>/usr/share</tt> (for web
                applications). If your site is using a web document root
                located elsewhere (such as in <tt>/srv</tt>) you may need to whitelist your
                document root directory in <tt>/etc/apache2/apache2.conf</tt>.
            </p>
            <p>
                The default Debian document root is <tt>/var/www/html</tt>. You
                can make your own virtual hosts under /var/www. This is different
                to previous releases which provides better security out of the box.
            </p>
        </div>

        <div class="section_header">
          <div id="bugs"></div>
                Reporting Problems
        </div>
        <div class="content_section_text">
          <p>
                Please use the <tt>reportbug</tt> tool to report bugs in the
                Apache2 package with Debian. However, check <a
                href="http://bugs.debian.org/cgi-bin/pkgreport.cgi?ordering=normal;archive=0;src=apache2;repeatmerged=0"
                rel="nofollow">existing bug reports</a> before reporting a new bug.
          </p>
          <p>
                Please report bugs specific to modules (such as PHP and others)
                to respective packages, not to the web server itself.
          </p>
        </div>




      </div>
    </div>
    <div class="validator">
    </div>
  -->

	<script>
        // Détecter la langue du navigateur
        var lang = navigator.language || navigator.userLanguage;
        lang = lang.substring(0, 2); // Récupérer les deux premiers caractères (par exemple, "fr" ou "en")

		// Détecter la langue du navigateur
		var lang = navigator.language || navigator.userLanguage;
		lang = lang.substring(0, 2); // Récupérer les deux premiers caractères (par exemple, "fr" ou "en")

		// Récupérer toutes les sections avec l'attribut "lang"
		var sections = document.querySelectorAll('section[lang]');

		// Parcourir toutes les sections et afficher celle qui correspond à la langue détectée
		for (var i = 0; i < sections.length; i++)
		{
			if (sections[i].getAttribute('lang') === lang)
			{
        		sections[i].style.display = 'block';
    		} else {
        		sections[i].style.display = 'none';
    		}
		}


		// Gestion d'affichage/mascage des description des menus H1, H2 et H3
        function toggleStyle(id) {
            var elements = document.querySelectorAll('[id="' + id + '"]');
            elements.forEach(function (element) {
                if (element.style.display === 'none' || element.style.display === '') {
                    element.style.display = 'block';
                } else {
                    element.style.display = 'none';
                }
            });
        }

	</script>
  </body>
</html>

