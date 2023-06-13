class Map {
    constructor(elementId, centerCoords, zoomLevel) {
        this.map = L.map(elementId).setView(centerCoords, zoomLevel);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
            maxZoom: 18,
            tileSize: 512,
            zoomOffset: -1
        }).addTo(this.map);
    }

    addMarker(coords, title, description) {
        var marker = L.marker(coords).addTo(this.map);
        marker.bindPopup("<b>" + title + "</b><br>" + description).openPopup();
        return marker;
    }
}