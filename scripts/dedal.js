function AddImg(url, id)
{
	// Ajoute l'élément img au div
	const video_img = document.getElementById( id );
	if ( video_img && (typeof video_img === "object") )
	{
		// Crée un élément img
		const img = document.createElement("img");
		img.src = url;
		img.style.width = "100px";
		img.style.height = "100px";
		video_img.appendChild(img);
	}
}

