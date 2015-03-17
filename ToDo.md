# DA IMPLEMENTARE #

- inserire il parametro che cambia il colore dei titoli nel fumetto anche nel modulo
- I have one suggestion for making it even more useful - an option to  specify a geographic name as a Key Reference instead of longitude/ latitude coordinates.  For a news-related site, the geotag for an  article is often just a city name, not precise coordinates.  In my  case, I am the administrator of an aviation-related website.  I would  like to geotag articles with their associated airport designator (e.g.  KLAX for Los Angeles airport - Google Maps understands an input such  as KLAX).  I realize that I can use you Geo web app to create the  coordinates (this is also a very nice application), but it would be  easier to allow non-technical editors using my site to simply type in  KLAX as a Key Reference instead of going through the extra steps of  generating the Lat/Long coordinates.

- Sarebbe bello poter implementare nel codice quello che ho fatto in questa pagina http://www.hydroeng.it/map.php cliccando nel marker oltre all'indirizzo è possibile avere anche le indicazioni stradali (Indicazioni: A qui - Da qui)

- The file mod\_contentmapt mpldefault.php ends up with a <br>. You may consider removing it as it adds an additional empty line if the ContentMap is at the end of a container (div, table, etc).<br>
<br>
- Da quanto ho capito leggendo il codice del modulo, non è possibile elencare nel baloon visualizzato per ogni marker l'elenco di tutti i contenuti che hanno come riferimento chiave le medesime coordinate<br>
Sono previsti sviluppi in tal senso ?<br>
<br>
- inserire in una mappa di google diversi punti di interessi appartenenti a categorie diverse e la possibilità di visualizzare solo una categoria (es. <a href='http://www.parcoabruzzo.it/map.php'>http://www.parcoabruzzo.it/map.php</a>)<br>
<a href='http://forum.joomla.it/index.php/topic,81188.0.html'>http://forum.joomla.it/index.php/topic,81188.0.html</a>

<h1>IMPLEMENTATO</h1>

- MOD E PLG there should be a way to configure the default view: map/satellite/hybrid (please see <a href='http://iveg.ro/resurse.html'>http://iveg.ro/resurse.html</a>)<br>
<br>
- SOLO MODULO the minimap in some cases covers markers so it also should have a way to minimize or remove it<br>
<br>
- SOLO MODULO Inserire la possibilità di mettere l'indirizzo anche nei file in release, come su turismo.eu<br>
<br>
- How can I make the terrain button shown? I want to present hiking destinations. There is terrain better than street map or satelite.<br>
<br>
- Usufruisco di questo post per chiede se in futuro sarà possibile inserire steet view.  ho cercato in giro ma non ho trovato nessun componente o plugin che possa integrare questa funzione nei siti.<br>
grazie.