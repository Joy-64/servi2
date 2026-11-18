/*PARA QUE FUNCIONE EL MAPA*/

var centroRosario = [-32.9468, -60.6393];

var limitesRosario = L.latLngBounds(
    [-33.05, -60.85],
    [-32.85, -60.55]
);

var map = L.map('map', {
    center: centroRosario,
    zoom: 13,
    minZoom: 12,           // antes 12.5: con zoomSnap=1 los zooms fraccionarios dan problemas
    maxBounds: limitesRosario,
    maxBoundsViscosity: 1,
    scrollWheelZoom: false // que la rueda del mouse no "atrape" la página
});

// Se activa el zoom con la rueda recién cuando hacen clic en el mapa
map.on('click', function () { map.scrollWheelZoom.enable(); });
map.on('mouseout', function () { map.scrollWheelZoom.disable(); });

/*
 * Los tiles de tile.openstreetmap.org se bloquean ("Access blocked") cuando la
 * página se abre con doble clic (file://) porque el navegador no manda Referer.
 * Servida por http (XAMPP / hosting) usamos OSM; abierta como archivo, Esri.
 */
if (location.protocol.startsWith('http')) {
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
} else {
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri'
    }).addTo(map);
}

L.marker(centroRosario).addTo(map)
    .bindPopup('<b>Rosario</b><br>Zona de cobertura ServiTrack')
    .openPopup();


/*AVISO dentro del mapa (en lugar de alert)*/
var aviso = document.getElementById('mapaAviso');
var avisoTimer;
function mostrarAviso(texto) {
    aviso.textContent = texto;
    aviso.classList.add('visible');
    clearTimeout(avisoTimer);
    avisoTimer = setTimeout(function () { aviso.classList.remove('visible'); }, 3500);
}


/*PARA EL GEOLOCALIZADOR*/
var marcaBusqueda = null;

L.Control.geocoder({
    defaultMarkGeocode: false,
    placeholder: 'Buscar dirección en Rosario...',
    errorMessage: 'No se encontró la dirección',
    geocoder: L.Control.Geocoder.nominatim({
        geocodingQueryParams: {
            viewbox: '-60.85,-32.85,-60.55,-33.05', // limita la búsqueda a Rosario
            bounded: 1,
            countrycodes: 'ar'
        }
    })
})
    .on('markgeocode', function (e) {
        var latlng = e.geocode.center;

        if (!limitesRosario.contains(latlng)) {
            mostrarAviso('Buscá una ubicación dentro de la zona de Rosario.');
            return;
        }

        if (marcaBusqueda) map.removeLayer(marcaBusqueda);
        marcaBusqueda = L.circleMarker(latlng, {
            radius: 18,
            color: '#3B82F6',
            fillColor: '#22D3EE',
            fillOpacity: 0.6,
            weight: 3
        }).addTo(map)
            .bindPopup(e.geocode.name.split(',').slice(0, 3).join(','))
            .openPopup();

        map.setView(latlng, 16);
    })
    .addTo(map);

// Por si el contenedor cambia de tamaño (menú, rotación del celular)
window.addEventListener('resize', function () { map.invalidateSize(); });
