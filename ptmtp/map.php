<?php
session_start();
include 'conn.php';
include 'auth.php';
require_roles(['forestry_officer']);
$page_title = 'GIS map';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="mb-3">
    <h1 class="page-title h2">Plantation GIS map</h1>
    <p class="text-muted">Interactive locations of all registered plantations.</p>
</div>
<div id="map"></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([-1.9441, 30.0619], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    fetch('get_pins.php').then(r => r.json()).then(pins => { const bounds = []; pins.forEach(p => { const point = [Number(p.lat), Number(p.lng)]; bounds.push(point); L.marker(point).addTo(map).bindPopup(`<b>${escapeHtml(p.name)}</b><br>Owner: ${escapeHtml(p.owner)}<br>Size: ${escapeHtml(p.size)} ha`) }); if (bounds.length) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 13 }); });
    function escapeHtml(v) { const d = document.createElement('div'); d.textContent = v ?? ''; return d.innerHTML }
</script><?php include 'includes/footer.php'; ?>
