
French and English version :

ENGLISH:
========
Management of Video Sequences Captured by an IP Camera
Video Sources
Wrapper, Middleware
Necessary tools


FRENCH:
=======
Gestion des séquences vidéos capturées par une camera
Sources des vidéos
Wrapper, Middleware
Outils neccessaire




ENGLISH VERSION:

* Management of Video Sequences Captured by an IP Camera


   This project is offered to you for managing intrusions detected by an
   IP camera.

   It is intended for those who do not wish to use the software provided
   with their camera or whose software is obsolete, as is the case with
   mine ( VHD-V61 ) where FlashPlayer is used.


* Video Sources

   The sequences are captured by Motion v4.3.2.
     * They are processed by the detect.sh script and video2image.php,
       which is launched via Motion when motion is detected.

     * on_movie_start /var/www/html/surveillance/bin/detect.sh --
       MovieStart file="%f" date="%Y%m%d" heure="%H:%M:%S" frame="%q"
       event=%v
     * on_movie_end /var/www/html/surveillance/bin/detect.sh -- MovieEnd
       file="%f" date="%Y%m%d" heure="%H:%M:%S" frame="%q" event=%v

     These scripts intervene on the videos produced by Motion.
     * Sending emails to report motion detection from the camera.
     * Extracting information and storing it in a JSON and XML file.

     * Duration
     * Video size
     * Number of frames

     The first image serving as a thumbnail (thumb).


* Wrapper, Middleware

   My camera is compatible with the ONVIF PTZ Control service, but its
   interface is obsolete; it only works with FlashPlayer. Therefore, I
   have added two methods to access remote surveillance both inside and
   outside my local network.

Method 1

     * Connection using routing and port forwarding.

     * This method allows for control of the camera's movements and direct
       access to the camera, thus full control.
     * I use a smartphone application compatible with ONVIF called IP Cam
       Viewer Lite, and camera management is done with no latency other
       than network quality.

Method 2

     * Use of an RTMP/HLS server.

     * This method provides a view of the camera from an HTML page but
       without control of its movements.
     * However, this method is not conclusive on my Raspberry Pi 2B and 4B
       since there is an average latency of 20 seconds on both devices.
     * It is, of course, possible to combine it with Method 1 for motion
       control.
     * Two pages complement this method. They allow for motion control and
       camera viewing.

     * [5]camera_control.php which uses the HTML/JS control interface
       integrated into the camera's firmware.
     * [6]camera.php which is loaded into a frame and allows for viewing
       the video stream generated by the RTMP/HLS server. This reproduces
       the original FlashPlayer interface provided by the manufacturer
       with the camera.

   It is therefore possible to replace the interface of my IP camera,
   which has become unusable.

   To assist you, two examples of NGINX configuration are located in the
   docs/nginx folder with useful comments.



FRENCH VERSION:

* Gestion des séquences vidéos capturées par une camera

   For Camera VHD-V61

   Ce projet vous est proposé pour gérer les intrusions détectées par une
   caméra IP

   Il est destiné à ceux qui ne souhaitent pas utiliser le software livré
   avec leur caméra ou dont celui-ci est obsolette comme c'est le cas pour
   la mienne ( VHD-V61 ) où FlashPlayer est employé

* Sources des vidéos

   Les séquences sont capturées par Motion v4.3.2

     * Elles sont traitées par le script detect.sh et video2image.php qui
       est lancé via Motion lors de la détection des mouvements

     * on_movie_start /var/www/html/surveillance/bin/detect.sh --
       MovieStart file="%f" date="%Y%m%d" heure="%H:%M:%S" frame="%q"
       event=%v
     * on_movie_end /var/www/html/surveillance/bin/detect.sh -- MovieEnd
       file="%f" date="%Y%m%d" heure="%H:%M:%S" frame="%q" event=%v

     Ces scripts interviennent sur les vidéos que produit Motion
     * Envoie d'email pour signaler une détection de mouvement depuis la
       caméra.
     * extrait les informations et les stock dans un fichier json et XML

     * durée
     * taile de la vidéo
     * nombre de frame

     la première image servant d'imagette (thumb)

* Warpper, middleware

   Ma caméra est compatible ONVIF PTZ Control service, mais son interface
   est obsolette, elle ne fonctionne qu'avec FlashPlayer, j'ai donc ajouté
   deux méthodes pour accéder à la surveillance à distance et en dehors de
   mon réseaui local

Méthode 1

     * Conection en utilisant le routage et redirection de port.

     * Cette méthode permet d'avoir un controle des mouvements de la
       caméra ainsi qu'un accès directe à la caméra. Donc un contrôle
       total
     * J'utilise une application sur smartphone compatible ONVIF, elle se
       nomme IP Cam Viewer Lite et la gestion de la caméra se fait sans
       aucune latence autre que la qualité du réseau !

Méthode 2

     * Usage d'un serveur RTMP/HLS

     * Cette méthode permet d'avoir une vue de la caméra depuis une page
       html mais ceci sans avoir de controle de ses mouvements.
     * Cepedant cette méthode n'est pas concluante sur mes 2 raspberry 2B
       et 4B, puisqu'il y une latence moyenne de de 20s sur les deux
       appareils.
     * Il est bien entendu possible de l'associer à la méthode 1 pour
       avoir le control du mouvement!
     * Deux pages complémente cette méthode. Elles permettent le controle
       des mouvements et le visionnage de la caméra.

     * [3]camera_control.php qui utilise l'interface html/js de contrôle
       intégrée dans le firmware de la caméra
     * [4]camera.php qui est chargé dans une frame et permet de visionner
       le flux vidéo généré par le serveur RTMP/HLS. Ceci reproduisant
       l'interface originalle en FlashPlayer que fournit le fabricant avec
       la caméra.

   Il est donc possible de remplacer l'interface de ma caméra IP qui est
   devenu inutilisable

   Pour vous aider, deux exemples de configuration NGINX se trouvent dans
   le dossier docs/nginx avec les commentaires utiles



* Outils neccessaire
	PHP 7.4.33 (cli)
	ffmpeg-php 3.2.2
	Motion 4.3.2
	bash
	javascript (optionel)



