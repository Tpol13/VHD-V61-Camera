	import net from "net/browser";

	const socket = new net.Socket();
		// Vérification connexion Motion port 8081
	//import net from "net";

		// Vérifie si la connexion TCP/IP est possible sur le port 8081
		const promise = new Promise((resolve, reject) => {
			const socket = new net.Socket();

			// Essaye de se connecter au port 8081
			socket.connect("localhost", 8081, () => {
				// Connexion réussie
				resolve(true);
			}, reject);
		});

		// Traite le résultat de la connexion
		promise.then(result => {
			if (result) {
				console.log("La connexion au port 8081 est possible");
			} else {
				console.log("La connexion au port 8081 est refusée");
			}
		});
